import React, { useState } from 'react';
import PropTypes from 'prop-types';
import RealValues from '../feature-possible-value/RealValues';
import deepcopy from 'deepcopy';

const RealValuesEditor = props => {
    const { featureId, possibleValueDomain } = props;
    const [values, setValues] = useState(props.values);

    // копипаста из feature-possible-value/Root, переделанная под values
    // TODO: вынести общий функционал
    const genericHandlers = {
        onChange(event, newValueProvider) {
            const matches = event.target.name.match(/^values\[(-?\d+)]\[(-?\d+)]\[(\w+)]$/);
            const featureId = +matches[1];
            const valueId = +matches[2];
            const fieldName = matches[3];

            const newValues = deepcopy(values);
            let valueToChange = newValues.find(v => v.id === valueId);
            valueToChange[fieldName] = newValueProvider(event, fieldName);

            setValues(newValues);
        },

        onDelete(featureId, valueId) {
            setValues(prevState => {
                return prevState.filter(v => v.id !== valueId);
            });
        },

        onAdd(featureId, newValue) {
            setValues(prevState => {
                prevState.push(newValue);
                return prevState;
            });
        },
    };
    const realHandlers = {
        onChange(event) {
            genericHandlers.onChange(event, (event, fieldName) => {
                let newValue;
                if (['lower', 'upper'].includes(fieldName)) {
                    newValue = +event.target.value;
                } else if (['lowerIsInclusive', 'upperIsInclusive'].includes(fieldName)) {
                    newValue = event.target.checked;
                } else {
                    throw new Error('be da s real handler');
                }
                return newValue;
            });
        },

        onDelete(featureId, valueId) {
            genericHandlers.onDelete(featureId, valueId);
        },

        onAdd(featureId, newValue) {
            genericHandlers.onAdd(featureId, newValue);
        },
    };

    return <>
        <p>Область возможных значений: {possibleValueDomain}</p>
        <p>Введите нормальные значения:</p>

        <RealValues
            featureId={featureId}
            values={values}
            onChange={realHandlers.onChange}
            onDelete={realHandlers.onDelete}
            onAdd={realHandlers.onAdd}
        />
    </>;
};

RealValuesEditor.propTypes = {
    featureId: PropTypes.number.isRequired,
    possibleValueDomain: PropTypes.string.isRequired,
    values: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        lower: PropTypes.number.isRequired,
        lowerIsInclusive: PropTypes.bool.isRequired,
        upper: PropTypes.number.isRequired,
        upperIsInclusive: PropTypes.bool.isRequired,
    })).isRequired,
};

export default RealValuesEditor;
