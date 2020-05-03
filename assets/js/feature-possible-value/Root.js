import React, {useEffect, useState} from 'react';
import PropTypes from 'prop-types';
import ScalarValues from "./ScalarValues";
import deepcopy from 'deepcopy';
import * as types from '../feature/types'

const Root = props => {
    const [features, setFeatures] = useState(props.features);
    const [selectedFeatureId, setSelectedFeatureId] = useState(0);

    const initialNewId = -1;
    const [latestNewId, setLatestNewId] = useState(initialNewId);

    const [updatedIds, setUpdatedIds] = useState(new Set());

    useEffect(() => {
        if (features.length > 0) {
            setSelectedFeatureId(features[0].id);
        }
    }, []);

    const onSelect = ({ target }) => {
        setSelectedFeatureId(+target.value);
    };

    const onScalarValueChange = ({ target }) => {
        const matches = target.name.match(/^values\[(-?\d+)]\[(-?\d+)]$/);
        const featureId = +matches[1];
        const valueId = +matches[2];

        const newValue = target.value;

        const newFeatures = deepcopy(features);

        const featureToChange = newFeatures.find(f => f.id === featureId);
        let valueToChange = featureToChange.possibleValues.find(v => v.id === valueId);

        valueToChange.value = newValue;

        setFeatures(newFeatures);

        if (valueId > initialNewId) {
            setUpdatedIds(prevState => prevState.add(valueId));
        }
    };

    const onScalarValueAdd = featureId => {
        const newFeatures = deepcopy(features);
        const featureToChange = newFeatures.find(f => f.id === featureId);
        featureToChange.possibleValues.push({
            id: latestNewId,
            value: '',
        });
        setFeatures(newFeatures);
        setLatestNewId(latestNewId - 1);
    };

    const [deletedIds, setDeletedIds] = useState(new Set());
    const onScalarValueDelete = (featureId, valueId) => {
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
    };

    const validatePossibleValue = (type, possibleValue) => {
        // TODO: validate not only scalar values
        if (possibleValue.value.length === 0) {
            return false;
        }
        return true;
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

            {/* TODO */}
            {/*<p>Тип: {Object.values(types).find(t => t.id === features[selectedFeatureId].type).name}</p>*/}

            {features.map(f => (
                <div key={f.id} hidden={f.id !== selectedFeatureId}>
                    <ScalarValues
                        featureId={f.id}
                        values={f.possibleValues}
                        onChange={onScalarValueChange}
                        onAdd={onScalarValueAdd}
                        onDelete={onScalarValueDelete}
                    />
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
