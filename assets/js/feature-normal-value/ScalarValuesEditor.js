import React, { useState } from 'react';
import PropTypes from 'prop-types';

const ScalarValuesEditor = ({ featureId, possibleValues }) => {
    const [checkedIdsMap, setCheckedIdsMap] = useState(possibleValues.map(v => ({ [v.id]: false })));

    const onChange = (valueId, checked) => {
        setCheckedIdsMap(prevState => {
            return {
                ...prevState,
                [valueId]: checked,
            };
        });
    };

    return <>
        <p>Выберите нормальные значения:</p>

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
};

export default ScalarValuesEditor;
