import React, {useState} from 'react';
import PropTypes from 'prop-types';
import ScalarValues from "./ScalarValues";
import deepcopy from 'deepcopy';
import * as types from '../feature/types'
import IntValues from "./IntValues";
import RealValues from "./RealValues";

const Root = props => {
    const [features, setFeatures] = useState(props.features);
    const [selectedFeatureId, setSelectedFeatureId] = useState(features.length > 0 ? features[0].id : 0);

    const initialNewId = -1;
    const [latestNewId, setLatestNewId] = useState(initialNewId);

    const [updatedIds, setUpdatedIds] = useState(new Set());
    const [deletedIds, setDeletedIds] = useState(new Set());

    if (features.length === 0) {
        return 'Нет признаков';
    }

    const onSelect = ({ target }) => {
        setSelectedFeatureId(+target.value);
    };

    const createNewValue = type => {
        switch (type) {
            case types.SCALAR.id:
                return {
                    id: latestNewId,
                    value: '',
                };
            case types.INT.id:
                return {
                    id: latestNewId,
                    lower: 0,
                    upper: 0,
                };
            case types.REAL.id:
                return {
                    id: latestNewId,
                    lower: 0.0,
                    lowerIsInclusive: false,
                    upper: 0.0,
                    upperIsInclusive: false,
                };
            default:
                throw new Error('be da s type');
        }
    };

    const genericHandlers = {
        onChange(event, newValueProvider) {
            const matches = event.target.name.match(/^values\[(-?\d+)]\[(-?\d+)]\[(\w+)]$/);
            const featureId = +matches[1];
            const valueId = +matches[2];
            const fieldName = matches[3];

            const newFeatures = deepcopy(features);

            const featureToChange = newFeatures.find(f => f.id === featureId);
            let valueToChange = featureToChange.possibleValues.find(v => v.id === valueId);

            valueToChange[fieldName] = newValueProvider(event, fieldName);

            setFeatures(newFeatures);

            if (valueId > initialNewId) {
                setUpdatedIds(prevState => prevState.add(valueId));
            }
        },

        onDelete(featureId, valueId) {
            const newFeatures = deepcopy(features);
            let featureToChange = newFeatures.find(f => f.id === featureId);
            featureToChange.possibleValues = featureToChange.possibleValues.filter(v => v.id !== valueId);

            setFeatures(newFeatures);

            if (valueId > initialNewId) {
                setDeletedIds(prevState => prevState.add(valueId));
                setUpdatedIds(prevState => {
                    prevState.delete(valueId);
                    return prevState;
                });
            }
        },

        onAdd(featureId) {
            const newFeatures = deepcopy(features);
            const featureToChange = newFeatures.find(f => f.id === featureId);

            featureToChange.possibleValues.push(createNewValue(features.find(f => f.id === featureId).type));

            setFeatures(newFeatures);
            setLatestNewId(latestNewId - 1);
        },
    };

    const scalarHandlers = {
        onChange(event) {
            genericHandlers.onChange(event, event => event.target.value);
        },

        onDelete(featureId, valueId) {
            genericHandlers.onDelete(featureId, valueId);
        },

        onAdd(featureId) {
            genericHandlers.onAdd(featureId);
        },
    };

    const intHandlers = {
        onChange(event) {
            genericHandlers.onChange(event, event => +event.target.value);
        },

        onDelete(featureId, valueId) {
            genericHandlers.onDelete(featureId, valueId);
        },

        onAdd(featureId) {
            genericHandlers.onAdd(featureId);
        },
    };

    const realHandlers = {
        onChange(event) {
            genericHandlers.onChange(event, (event, fieldName) => {
                let newValue;
                if (['lower', 'upper'].includes(fieldName)) {
                    newValue = +event.target.value;
                } else if (['lowerIsInclusive', 'upperIsInclusive'].includes(fieldName)) {
                    newValue = event.target.checked;
                } else {
                    throw new Error('be da s real handler');
                }
                return newValue;
            });
        },

        onDelete(featureId, valueId) {
            genericHandlers.onDelete(featureId, valueId);
        },

        onAdd(featureId) {
            genericHandlers.onAdd(featureId);
        },
    };

    const createValuesEditor = feature => {
        switch (feature.type) {
            case types.SCALAR.id:
                return (
                    <ScalarValues
                        featureId={feature.id}
                        values={feature.possibleValues}
                        onChange={scalarHandlers.onChange}
                        onDelete={scalarHandlers.onDelete}
                        onAdd={scalarHandlers.onAdd}
                    />
                );
            case types.INT.id:
                return (
                    <IntValues
                        featureId={feature.id}
                        values={feature.possibleValues}
                        onChange={intHandlers.onChange}
                        onDelete={intHandlers.onDelete}
                        onAdd={intHandlers.onAdd}
                    />
                );
            case types.REAL.id:
                return (
                    <RealValues
                        featureId={feature.id}
                        values={feature.possibleValues}
                        onChange={realHandlers.onChange}
                        onDelete={realHandlers.onDelete}
                        onAdd={realHandlers.onAdd}
                    />
                );
            default:
                throw new Error('be da s feature type');
        }
    };

    const getTypeNameByFeatureId = featureId => {
        const feature = features.find(f => f.id === featureId);
        const type = Object.values(types).find(t => t.id === feature.type);
        return type.name.toLowerCase();
    };

    const validatePossibleValue = (type, possibleValue) => {
        if ([types.INT.id, types.REAL.id].includes(type)) {
            return true;
        }

        return possibleValue.value.length !== 0;
    };

    const validate = () => {
        for (const feature of features) {
            for (const possibleValue of feature.possibleValues) {
                const isValid = validatePossibleValue(feature.type, possibleValue);
                if (!isValid) {
                    return [false, feature.id];
                }
            }
        }
        return [true, null];
    };

    const onSubmit = () => {
        const [isValid, firstInvalidFeatureId] = validate();
        if (!isValid) {
            setSelectedFeatureId(firstInvalidFeatureId);
        }
    };

    return (
        <form method="post">
            <select value={selectedFeatureId} onChange={onSelect}>
                {features.map(f => (
                    <React.Fragment key={f.id}>
                        <option value={f.id}>
                            {f.name} (признак #{f.id})
                        </option>
                    </React.Fragment>
                ))}
            </select>

            <p>Тип: {getTypeNameByFeatureId(selectedFeatureId)}</p>

            {features.map(f => (
                <div key={f.id} hidden={f.id !== selectedFeatureId}>
                    {createValuesEditor(f)}
                    <br />
                </div>
            ))}

            <input type="hidden" name="updatedIds" value={Array.from(updatedIds).join(',')} />
            <input type="hidden" name="deletedIds" value={Array.from(deletedIds).join(',')} />

            <button onClick={onSubmit}>Сохранить</button>
        </form>
    );
};

Root.propTypes = {
    features: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        type: PropTypes.number.isRequired,
        possibleValues: PropTypes.arrayOf(PropTypes.object.isRequired).isRequired,
    })).isRequired,
};

export default Root;
