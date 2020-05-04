import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { INT_MIN, INT_MAX } from "./constraints";

const IntValues = ({ featureId, values, onChange, onDelete, onAdd }) => {
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
        onAdd(featureId, {
            id: latestNewId,
            lower: 0,
            upper: 0,
        });
        setLatestNewId(latestNewId - 1);
    };

    return <>
        <input type="hidden" name={`values[${featureId}][updatedIds]`} value={Array.from(updatedIds).join(',')} />
        <input type="hidden" name={`values[${featureId}][deletedIds]`} value={Array.from(deletedIds).join(',')} />

        {values.map(v => (
            <React.Fragment key={`${featureId}-${v.id}`}>
                Нижняя граница:{' '}
                <input
                    type="number"
                    name={`values[${featureId}][${v.id}][lower]`}
                    value={v.lower}
                    onChange={event => { onChange(event); onChangeDecorated(v.id); }}
                    min={INT_MIN}
                    max={INT_MAX}
                    required={true}
                />
                <br />

                Верхняя граница:{' '}
                <input
                    type="number"
                    name={`values[${featureId}][${v.id}][upper]`}
                    value={v.upper}
                    onChange={event => { onChange(event); onChangeDecorated(v.id); }}
                    min={INT_MIN}
                    max={INT_MAX}
                    required={true}
                />
                <br />
                <button onClick={event => { event.preventDefault(); onDeleteDecorated(featureId, v.id) }}>
                    Удалить
                </button>
                <br />
                <br />
            </React.Fragment>
        ))}
        <button onClick={event => { event.preventDefault(); onAddDecorated(featureId); }}>
            Добавить целочисленное значение
        </button>
    </>;
};

IntValues.propTypes = {
    featureId: PropTypes.number.isRequired,
    values: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        lower: PropTypes.number.isRequired,
        upper: PropTypes.number.isRequired,
    })).isRequired,
    onChange: PropTypes.func.isRequired,
    onDelete: PropTypes.func.isRequired,
    onAdd: PropTypes.func.isRequired,
};

export default IntValues;
