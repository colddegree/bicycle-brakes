import React from 'react';
import PropTypes from 'prop-types';

const ScalarValues = ({ featureId, values, onChange, onDelete, onAdd }) => {
    return <>
        {values.map(v => (
            <React.Fragment key={`${featureId}-${v.id}`}>
                <input
                    name={`values[${featureId}][${v.id}][value]`}
                    value={v.value}
                    onChange={onChange}
                    maxLength={255}
                    size={50}
                    required={true}
                />{' '}
                <button onClick={() => onDelete(featureId, v.id)}>Удалить</button>
                <br />
            </React.Fragment>
        ))}
        <br />
        <button onClick={event => { event.preventDefault(); onAdd(featureId); }}>Добавить скалярное значение</button>
    </>;
};

ScalarValues.propTypes = {
    featureId: PropTypes.number.isRequired,
    values: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        value: PropTypes.string.isRequired,
    })).isRequired,
    onChange: PropTypes.func.isRequired,
    onDelete: PropTypes.func.isRequired,
    onAdd: PropTypes.func.isRequired,
};

export default ScalarValues;
