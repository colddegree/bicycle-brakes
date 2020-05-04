import React, { useState } from 'react';
import PropTypes from 'prop-types';
import IntValues from '../feature-possible-value/IntValues';
import deepcopy from 'deepcopy';

const IntValuesEditor = ({ featureId, possibleValueDomain, normalValues }) => {
    const [values, setValues] = useState(normalValues);

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
    const intHandlers = {
        onChange(event) {
            genericHandlers.onChange(event, event => +event.target.value);
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

        <IntValues
            featureId={featureId}
            values={values}
            onChange={intHandlers.onChange}
            onDelete={intHandlers.onDelete}
            onAdd={intHandlers.onAdd}
        />
    </>;
};

IntValuesEditor.propTypes = {
    featureId: PropTypes.number.isRequired,
    possibleValueDomain: PropTypes.string.isRequired,
    normalValues: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        lower: PropTypes.number.isRequired,
        upper: PropTypes.number.isRequired,
    })).isRequired,
};

export default IntValuesEditor;
