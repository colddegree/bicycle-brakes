import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { REAL_MIN, REAL_MAX } from "./constraints";

const RealValues = ({ featureId, fieldPathPrefix, values, onChange, onDelete, onAdd }) => {
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
            lower: 0.0,
            lowerIsInclusive: false,
            upper: 0.0,
            upperIsInclusive: false,
        });
        setLatestNewId(latestNewId - 1);
    };

    return <>
        <input type="hidden" name={`${fieldPathPrefix}[updatedIds]`} value={Array.from(updatedIds).join(',')} />
        <input type="hidden" name={`${fieldPathPrefix}[deletedIds]`} value={Array.from(deletedIds).join(',')} />

        {values.map(v => (
            <React.Fragment key={`${featureId}-${v.id}`}>
                <label>
                    Нижняя граница:{' '}
                    <input
                        type="number"
                        name={`${fieldPathPrefix}[${v.id}][lower]`}
                        value={Number(v.lower).toFixed(2)}
                        onChange={event => { onChange(event); onChangeDecorated(v.id); }}
                        min={REAL_MIN}
                        max={REAL_MAX}
                        step={0.01}
                        required={true}
                    />
                </label>{' '}
                <label>
                    <input
                        type="checkbox"
                        name={`${fieldPathPrefix}[${v.id}][lowerIsInclusive]`}
                        checked={v.lowerIsInclusive}
                        onChange={event => { onChange(event); onChangeDecorated(v.id); }}
                    />{' '}
                    включительно
                </label>
                <br />

                <label>
                    Верхняя граница:{' '}
                    <input
                        type="number"
                        name={`${fieldPathPrefix}[${v.id}][upper]`}
                        value={Number(v.upper).toFixed(2)}
                        onChange={event => { onChange(event); onChangeDecorated(v.id); }}
                        min={REAL_MIN}
                        max={REAL_MAX}
                        step={0.01}
                        required={true}
                    />
                </label>{' '}
                <label>
                    <input
                        type="checkbox"
                        name={`${fieldPathPrefix}[${v.id}][upperIsInclusive]`}
                        checked={v.upperIsInclusive}
                        onChange={event => { onChange(event); onChangeDecorated(v.id); }}
                    />{' '}
                    включительно
                </label>
                <br />
                <button onClick={() => onDeleteDecorated(featureId, v.id)}>Удалить</button>
                <br />
                <br />
            </React.Fragment>
        ))}
        <button onClick={event => { event.preventDefault(); onAddDecorated(featureId); }}>
            Добавить вещественное значение
        </button>
    </>;
};

RealValues.propTypes = {
    featureId: PropTypes.number.isRequired,
    fieldPathPrefix: PropTypes.string.isRequired,
    values: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        lower: PropTypes.number.isRequired,
        lowerIsInclusive: PropTypes.bool.isRequired,
        upper: PropTypes.number.isRequired,
        upperIsInclusive: PropTypes.bool.isRequired,
    })).isRequired,
    onChange: PropTypes.func.isRequired,
    onDelete: PropTypes.func.isRequired,
    onAdd: PropTypes.func.isRequired,
};

export default RealValues;
