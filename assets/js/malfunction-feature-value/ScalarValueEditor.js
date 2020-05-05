import React, { useState } from 'react';
import PropTypes from 'prop-types';

const ScalarValuesEditor = ({ malfunctionId, featureId, possibleValues, values }) => {
    const [checkedFeatureIdsMap, setCheckedFeatureIdsMap] = useState(possibleValues.reduce(
        (idsMap, pv) => {
            idsMap[pv.id] = !!values.find(v => v.id === pv.id);
            return idsMap;
        },
        {},
    ));
    const [updatedFeatureIds, setUpdatedFeatureIds] = useState(new Set());

    if (possibleValues.length < 1) {
        return 'Нет возможных значений';
    }

    const onChange = (valueId, checked) => {
        setCheckedFeatureIdsMap(prevState => {
            return {
                ...prevState,
                [valueId]: checked,
            };
        });
        setUpdatedFeatureIds(prevState => prevState.add(valueId));
    };

    return <>
        {updatedFeatureIds.size > 0 && (
            <>
                <input
                    type="hidden"
                    name={`malfunctions[${malfunctionId}][id]`}
                    value={malfunctionId}
                />
                <input
                    type="hidden"
                    name={`malfunctions[${malfunctionId}][scalarFeatures][${featureId}][id]`}
                    value={featureId}
                />
            </>
        )}

        {possibleValues.map(v => (
            <React.Fragment key={v.id}>
                {checkedFeatureIdsMap[v.id] && updatedFeatureIds.size > 0 && (
                    <>
                        <input
                            type="hidden"
                            name={`malfunctions[${malfunctionId}][scalarFeatures][${featureId}][selectedIds][]`}
                            value={v.id}
                        />
                    </>
                )}

                <label>
                    <input
                        type="checkbox"
                        checked={checkedFeatureIdsMap[v.id]}
                        onChange={event => onChange(v.id, event.target.checked)}
                    />{' '}
                    {v.value}
                </label>
                <br />
            </React.Fragment>
        ))}

        {Array.from(updatedFeatureIds).map(id => (
            <input
                key={id}
                type="hidden"
                name={`malfunctions[${malfunctionId}][scalarFeatures][${featureId}][updatedIds][]`}
                value={id}
            />
        ))}
    </>;
};

ScalarValuesEditor.propTypes = {
    malfunctionId: PropTypes.number.isRequired,
    featureId: PropTypes.number.isRequired,
    possibleValues: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        value: PropTypes.string.isRequired,
    })).isRequired,
    values: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        value: PropTypes.string.isRequired,
    })).isRequired,
};

export default ScalarValuesEditor;
