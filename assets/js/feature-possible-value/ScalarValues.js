import React, { useState } from 'react';
import PropTypes from 'prop-types';

const ScalarValues = ({ featureId, values, onChange, onDelete, onAdd }) => {
    const [updatedIds, setUpdatedIds] = useState(new Set());
    const [deletedIds, setDeletedIds] = useState(new Set());

    const initialNewId = -1;
    const [latestNewId, setLatestNewId] = useState(initialNewId);

    const onChangeDecorated = valueId => {
        if (valueId > initialNewId) {
            setUpdatedIds(prevState => prevState.add(valueId));
        }
    };

    const onDeleteDecorated = (featureId, valueId) => {
        onDelete(featureId, valueId);
        if (valueId > initialNewId) {
            setDeletedIds(prevState => prevState.add(valueId));
            setUpdatedIds(prevState => {
                prevState.delete(valueId);
                return prevState;
            });
        }
    };

    const onAddDecorated = featureId => {
        onAdd(featureId, { id: latestNewId, value: '' });
        setLatestNewId(latestNewId - 1);
    };

    return <>
        <input type="hidden" name={`values[${featureId}][updatedIds]`} value={Array.from(updatedIds).join(',')} />
        <input type="hidden" name={`values[${featureId}][deletedIds]`} value={Array.from(deletedIds).join(',')} />

        {values.map(v => (
            <React.Fragment key={`${featureId}-${v.id}`}>
                <input
                    name={`values[${featureId}][${v.id}][value]`}
                    value={v.value}
                    onChange={event => { onChange(event); onChangeDecorated(v.id); }}
                    maxLength={255}
                    size={50}
                    required={true}
                />{' '}
                <button onClick={() => onDeleteDecorated(featureId, v.id)}>Удалить</button>
                <br />
            </React.Fragment>
        ))}
        <br />
        <button onClick={event => { event.preventDefault(); onAddDecorated(featureId); }}>
            Добавить скалярное значение
        </button>
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
