import React, { useState } from 'react';
import PropTypes from 'prop-types';
import * as types from '../feature/types'
import ScalarValuesEditor from './ScalarValueEditor';
import IntValuesEditor from '../feature-normal-value/IntValuesEditor';
import RealValuesEditor from '../feature-normal-value/RealValuesEditor';

const Root = ({ malfunctions }) => {
    const [selectedMalfunction, setSelectedMalfunction] = useState(
        malfunctions.length > 0 ? malfunctions[0] : {},
    );
    const [selectedFeature, setSelectedFeature] = useState(
        malfunctions.length > 0 && malfunctions[0].features.length > 0
            ? malfunctions[0].features[0]
            : {},
    );

    if (malfunctions.length < 1) {
        return 'Нет неисправностей';
    }

    const onMalfunctionSelect = event => {
        const newMalfunctionId = +event.target.value;
        setSelectedMalfunction(malfunctions.find(m => m.id === newMalfunctionId));

        // reset selected feature id
        const features = malfunctions.find(m => m.id === newMalfunctionId).features;
        setSelectedFeature(features.length > 0 ? features[0] : {});
    };

    const onFeatureSelect = event => {
        const featureId = +event.target.value;
        setSelectedFeature(selectedMalfunction.features.find(f => f.id === featureId));
    };

    const getTypeNameByFeature = feature => {
        const type = Object.values(types).find(t => t.id === feature.type);
        if (!type) {
            return '';
        }
        return type.name.toLowerCase();
    };

    const createValuesEditor = (malfunction, feature) => {
        switch (feature.type) {
            case types.SCALAR.id:
                return (
                    <ScalarValuesEditor
                        malfunctionId={malfunction.id}
                        featureId={feature.id}
                        possibleValues={feature.possibleScalarValues}
                        values={feature.values}
                    />
                );
            case types.INT.id:
                return (
                    <IntValuesEditor
                        actionText={'Введите значения признаков выбранной неисправности:'}
                        featureId={feature.id}
                        fieldPathPrefix={`malfunctions[${malfunction.id}][features][int][${feature.id}]`}
                        pathRegex={/^malfunctions\[-?\d+]\[features]\[int]\[(-?\d+)]\[(-?\d+)]\[(\w+)]$/}
                        possibleValueDomain={feature.possibleValueDomain}
                        values={feature.values}
                    />
                );
            case types.REAL.id:
                return (
                    <RealValuesEditor
                        actionText={'Введите значения признаков выбранной неисправности:'}
                        featureId={feature.id}
                        fieldPathPrefix={`malfunctions[${malfunction.id}][features][real][${feature.id}]`}
                        pathRegex={/^malfunctions\[-?\d+]\[features]\[real]\[(-?\d+)]\[(-?\d+)]\[(\w+)]$/}
                        possibleValueDomain={feature.possibleValueDomain}
                        values={feature.values}
                    />
                );
            default:
                throw new Error('be da s feature type');
        }
    };

    return (
        <form method="post">
            <label>
                Неисправность
                <br />
                <select value={selectedMalfunction.id} onChange={onMalfunctionSelect}>
                    {malfunctions.map(m => (
                        <React.Fragment key={m.id}>
                            <option value={m.id}>{m.name} (#{m.id})</option>
                        </React.Fragment>
                    ))}
                </select>
            </label>
            <br />
            <br />

            {malfunctions.map(m => (
                <div key={m.id} hidden={m.id !== selectedMalfunction.id}>
                    {m.features.length < 1 ? (
                        <p>В клинической картине выбранной неисправности нет признаков</p>
                    ) : (
                        <>
                            <label>
                                Признак
                                <br />
                                <select value={selectedFeature.id} onChange={onFeatureSelect}>
                                    {m.features.map(f => (
                                        <React.Fragment key={f.id}>
                                            <option value={f.id}>{f.name} (#{f.id})</option>
                                        </React.Fragment>
                                    ))}
                                </select>
                            </label>

                            <p>Тип: {getTypeNameByFeature(selectedFeature)}</p>

                            <p>Значения</p>
                            {m.features.map(f => (
                                <div key={f.id} hidden={f.id !== selectedFeature.id}>
                                    {createValuesEditor(m, f)}
                                    <br />
                                </div>
                            ))}
                        </>
                    )}
                </div>
            ))}

            <button>Сохранить</button>
        </form>
    );
};

Root.propTypes = {
    malfunctions: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        features: PropTypes.arrayOf(PropTypes.shape({
            id: PropTypes.number.isRequired,
            name: PropTypes.string.isRequired,
            type: PropTypes.number.isRequired,
            values: PropTypes.arrayOf(PropTypes.oneOfType([
                PropTypes.shape({
                    id: PropTypes.number.isRequired,
                    value: PropTypes.string.isRequired,
                }),
                PropTypes.shape({
                    id: PropTypes.number.isRequired,
                    lower: PropTypes.number.isRequired,
                    upper: PropTypes.number.isRequired,
                }),
                PropTypes.shape({
                    id: PropTypes.number.isRequired,
                    lower: PropTypes.number.isRequired,
                    lowerIsInclusive: PropTypes.bool.isRequired,
                    upper: PropTypes.number.isRequired,
                    upperIsInclusive: PropTypes.bool.isRequired,
                }),
            ])).isRequired,
            possibleScalarValues: PropTypes.arrayOf(PropTypes.shape({
                id: PropTypes.number.isRequired,
                value: PropTypes.string.isRequired,
            })),
            possibleValueDomain: PropTypes.string,
        })).isRequired,
    })).isRequired,
};

export default Root;
