import React, { useState } from 'react';
import PropTypes from 'prop-types';

const FeaturePicker = ({ malfunctionId, selectedFeatureIds, allFeatures }) => {
    const [checkedIdsMap, setCheckedIdsMap] = useState(allFeatures.reduce(
        (idsMap, f) => {
            idsMap[f.id] = selectedFeatureIds.includes(f.id);
            return idsMap;
        },
        {},
    ));
    const [updatedIds, setUpdatedIds] = useState(new Set());

    const onChange = (featureId, checked) => {
        setCheckedIdsMap(prevState => {
            return {
                ...prevState,
                [featureId]: checked,
            };
        });
        setUpdatedIds(prevState => prevState.add(featureId));
    };

    return <>
        <p>Выберите признаки, которые образуют клиническую картину этой неисправности:</p>

        {Array.from(updatedIds).map(id => (
            <input key={id} type="hidden" name={`malfunctions[${malfunctionId}][updatedIds][]`} value={id} />
        ))}

        {allFeatures.map(f => (
            <React.Fragment key={f.id}>
                {checkedIdsMap[f.id] && (
                    <input
                        type="hidden"
                        name={`malfunctions[${malfunctionId}][selectedFeatureIds][]`}
                        value={f.id}
                    />
                )}

                <label>
                    <input
                        type="checkbox"
                        checked={checkedIdsMap[f.id]}
                        onChange={event => onChange(f.id, event.target.checked)}
                    />{' '}
                    {f.name}
                </label>
                <br />
            </React.Fragment>
        ))}
    </>;
};

FeaturePicker.propTypes = {
    malfunctionId: PropTypes.number.isRequired,
    selectedFeatureIds: PropTypes.arrayOf(PropTypes.number).isRequired,
    allFeatures: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
    })).isRequired,
};

export default FeaturePicker;
