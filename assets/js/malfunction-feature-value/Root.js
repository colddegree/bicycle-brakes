import React, { useState } from 'react';
import PropTypes from 'prop-types';
import * as types from '../feature/types'

const Root = ({ malfunctions }) => {
    const [selectedMalfunctionId, setSelectedMalfunctionId] = useState(
        malfunctions.length > 0
            ? malfunctions[0].id
            : null,
    );
    const [selectedFeatureId, setSelectedFeatureId] = useState(
        malfunctions.length > 0 && malfunctions[0].features.length > 0
            ? malfunctions[0].features[0].id
            : null,
    );

    if (malfunctions.length < 1) {
        return 'Нет неисправностей';
    }

    const onMalfunctionSelect = event => {
        const newMalfunctionId = +event.target.value;
        setSelectedMalfunctionId(newMalfunctionId);

        // reset selected feature id
        const features = malfunctions.find(m => m.id === newMalfunctionId).features;
        setSelectedFeatureId(features.length > 0 ? features[0].id : 0);
    };

    const onFeatureSelect = event => {
        setSelectedFeatureId(+event.target.value);
    };

    const getTypeNameByFeature = ({ type }) => {
        return Object.values(types).find(t => t.id === type).name.toLowerCase();
    };

    // TODO
    const createValuesEditor = feature => {
        switch (feature.type) {
            case types.SCALAR.id:
                return 'ScalarValuesEditor';
            case types.INT.id:
                return 'IntValuesEditor';
            case types.REAL.id:
                return 'RealValuesEditor';
            default:
                throw new Error('be da s feature type');
        }
    };

    return (
        <form method="post">
            <label>
                Неисправность
                <br />
                <select value={selectedMalfunctionId} onChange={onMalfunctionSelect}>
                    {malfunctions.map(m => (
                        <React.Fragment key={m.id}>
                            <option value={m.id}>
                                {m.name} (неисправность #{m.id})
                            </option>
                        </React.Fragment>
                    ))}
                </select>
            </label>
            <br />
            <br />

            {malfunctions.find(m => m.id === selectedMalfunctionId).features.length < 1 ? (
                <p>В клинической картине выбранной неисправности нет признаков</p>
            ) : (
                <>
                    <label>
                        Признак
                        <br />
                        <select value={selectedFeatureId} onChange={onFeatureSelect}>
                            {malfunctions.find(m => m.id === selectedMalfunctionId).features.map(f => (
                                <React.Fragment key={f.id}>
                                    <option value={f.id}>
                                        {f.name} (признак #{f.id})
                                    </option>
                                </React.Fragment>
                            ))}
                        </select>
                    </label>

                    <p>Тип: {getTypeNameByFeature(malfunctions.find(m => m.id === selectedMalfunctionId).features.find(
                        f => f.id === selectedFeatureId))}</p>

                    {malfunctions.find(m => m.id === selectedMalfunctionId).features.map(f => (
                        <div key={f.id} hidden={f.id !== selectedFeatureId}>
                            {createValuesEditor(f)}
                            <br />
                        </div>
                    ))}
                </>
            )}

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
        })).isRequired,
    })).isRequired,
};

export default Root;
