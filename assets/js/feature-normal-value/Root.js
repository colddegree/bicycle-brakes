import React, { useState } from 'react';
import PropTypes from 'prop-types';
import * as types from '../feature/types'
import ScalarValuesEditor from './ScalarValuesEditor';

const Root = props => {
    const [features, setFeatures] = useState(props.features);
    const [selectedFeatureId, setSelectedFeatureId] = useState(features.length > 0 ? features[0].id : 0);

    if (features.length === 0) {
        return 'Нет признаков';
    }

    const onSelect = ({ target }) => {
        setSelectedFeatureId(+target.value);
    };

    const getTypeNameByFeatureId = featureId => {
        const feature = features.find(f => f.id === featureId);
        const type = Object.values(types).find(t => t.id === feature.type);
        return type.name.toLowerCase();
    };

    const onSubmit = event => {
        event.preventDefault();
    };

    const createValuesEditor = feature => {
        switch (feature.type) {
            case types.SCALAR.id:
                return (
                    <ScalarValuesEditor
                        featureId={feature.id}
                        possibleValues={feature.possibleValues}
                        normalValues={feature.normalValues}
                    />
                );
            case types.INT.id:
            case types.REAL.id:
            default:
                throw new Error('be da s feature type');
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

            <button onClick={onSubmit}>Сохранить</button>
        </form>
    );
};

Root.propTypes = {
    features: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        type: PropTypes.number.isRequired,
        possibleValues: PropTypes.array.isRequired,
        normalValues: PropTypes.arrayOf(PropTypes.object.isRequired).isRequired,
    })).isRequired,
};

export default Root;
