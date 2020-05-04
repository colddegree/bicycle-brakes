import React, { useState } from 'react';
import PropTypes from 'prop-types';

const ScalarValuesEditor = ({ featureId, possibleValues, normalValues }) => {
    const [checkedIdsMap, setCheckedIdsMap] = useState(possibleValues.reduce(
        (idsMap, pv) => {
            idsMap[pv.id] = !!normalValues.find(nv => nv.id === pv.id);
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
        <p>Выберите нормальные значения:</p>

        <input type="hidden" name={`values[${featureId}][updatedIds]`} value={Array.from(updatedIds).join(',')} />

        {possibleValues.map(v => (
            <React.Fragment key={v.id}>
                <label>
                    <input
                        type="checkbox"
                        name={`values[${featureId}][${v.id}][checked]`}
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
    featureId: PropTypes.number.isRequired,
    possibleValues: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        value: PropTypes.string.isRequired,
    })).isRequired,
    normalValues: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        value: PropTypes.string.isRequired,
    })).isRequired,
};

export default ScalarValuesEditor;
