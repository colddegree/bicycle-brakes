import React from 'react';
import PropTypes from 'prop-types';
import { REAL_MIN, REAL_MAX } from "./constraints";

const RealValues = ({ featureId, values, onChange, onDelete, onAdd }) => {
    return <>
        {values.map(v => (
            <React.Fragment key={`${featureId}-${v.id}`}>
                Нижняя граница:{' '}
                <input
                    type="number"
                    name={`values[${featureId}][${v.id}][lower]`}
                    value={Number(v.lower).toFixed(2)}
                    onChange={onChange}
                    min={REAL_MIN}
                    max={REAL_MAX}
                    step={0.01}
                    required={true}
                />{' '}
                <label>
                    <input
                        type="checkbox"
                        name={`values[${featureId}][${v.id}][lowerIsInclusive]`}
                        checked={v.lowerIsInclusive}
                        onChange={onChange}
                    />{' '}
                    включительно
                </label>
                <br />

                Верхняя граница:{' '}
                <input
                    type="number"
                    name={`values[${featureId}][${v.id}][upper]`}
                    value={Number(v.upper).toFixed(2)}
                    onChange={onChange}
                    min={REAL_MIN}
                    max={REAL_MAX}
                    step={0.01}
                    required={true}
                />{' '}
                <label>
                    <input
                        type="checkbox"
                        name={`values[${featureId}][${v.id}][upperIsInclusive]`}
                        checked={v.upperIsInclusive}
                        onChange={onChange}
                    />{' '}
                    включительно
                </label>
                <br />
                <button onClick={() => onDelete(featureId, v.id)}>Удалить</button>
                <br />
                <br />
            </React.Fragment>
        ))}
        <button onClick={event => { event.preventDefault(); onAdd(featureId); }}>Добавить вещественное значение</button>
    </>;
};

RealValues.propTypes = {
    featureId: PropTypes.number.isRequired,
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
