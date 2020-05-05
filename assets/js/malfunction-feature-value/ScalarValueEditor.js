import React, { useState } from 'react';
import PropTypes from 'prop-types';

const ScalarValuesEditor = ({ malfunctionId, possibleValues, values }) => {
    const [checkedIdsMap, setCheckedIdsMap] = useState(possibleValues.reduce(
        (idsMap, pv) => {
            idsMap[pv.id] = !!values.find(v => v.id === pv.id);
            return idsMap;
        },
        {},
    ));
    const [updatedIds, setUpdatedIds] = useState(new Set());

    if (possibleValues.length < 1) {
        return 'Нет возможных значений';
    }

    const onChange = (valueId, checked) => {
        setCheckedIdsMap(prevState => {
            return {
                ...prevState,
                [valueId]: checked,
            };
        });
        setUpdatedIds(prevState => prevState.add(valueId));
    };

    return <>
        {Array.from(updatedIds).map(id => (
            <input key={id} type="hidden" name={`malfunctions[${malfunctionId}][updatedIds][]`} value={id} />
        ))}

        {possibleValues.map(v => (
            <React.Fragment key={v.id}>
                {checkedIdsMap[v.id] && (
                    <input
                        type="hidden"
                        name={`malfunctions[${malfunctionId}][selectedScalarValueIds][]`}
                        value={v.id}
                    />
                )}

                <label>
                    <input
                        type="checkbox"
                        checked={checkedIdsMap[v.id]}
                        onChange={event => onChange(v.id, event.target.checked)}
                    />{' '}
                    {v.value}
                </label>
                <br />
            </React.Fragment>
        ))}
    </>;
};

ScalarValuesEditor.propTypes = {
    malfunctionId: PropTypes.number.isRequired,
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
