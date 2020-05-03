import React from 'react';
import PropTypes from 'prop-types';
import { INT_MAX, INT_MIN } from "./constraints";

const IntValues = ({ featureId, values }) => {
    return <>
        {values.map(v => (
            <React.Fragment key={`${featureId}-${v.id}`}>
                Нижняя граница:{' '}
                <input
                    type="number"
                    name={`values[${featureId}][${v.id}][lower]`}
                    value={v.lower}
                    // onChange={onChange}
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
                    // onChange={onChange}
                    min={INT_MIN}
                    max={INT_MAX}
                    required={true}
                />
                <br />
                <br />
            </React.Fragment>
        ))}
    </>
};

IntValues.propTypes = {
    featureId: PropTypes.number.isRequired,
    values: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        lower: PropTypes.number.isRequired,
        upper: PropTypes.number.isRequired,
    })).isRequired,
    // TODO
    // onChange: PropTypes.func.isRequired,
    // onDelete: PropTypes.func.isRequired,
    // onAdd: PropTypes.func.isRequired,
};

export default IntValues;
