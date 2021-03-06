import React, { useState } from 'react';
import PropTypes from 'prop-types';
import RealValues from '../feature-possible-value/RealValues';
import deepcopy from 'deepcopy';

const RealValuesEditor = props => {
    const { actionText, featureId, fieldPathPrefix, pathRegex, possibleValueDomain } = props;
    const [values, setValues] = useState(props.values);

    // копипаста из feature-possible-value/Root, переделанная под values
    // TODO: вынести общий функционал
    const genericHandlers = {
        onChange(event, newValueProvider) {
            const matches = event.target.name.match(pathRegex || /^values\[(-?\d+)]\[(-?\d+)]\[(\w+)]$/);
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
        {props.normalValueDomain && (
            <p>Область нормальных значений: {props.normalValueDomain}</p>
        )}
        <p>{actionText}</p>

        <RealValues
            featureId={featureId}
            fieldPathPrefix={fieldPathPrefix}
            values={values}
            onChange={realHandlers.onChange}
            onDelete={realHandlers.onDelete}
            onAdd={realHandlers.onAdd}
        />
    </>;
};

RealValuesEditor.propTypes = {
    actionText: PropTypes.string.isRequired,
    featureId: PropTypes.number.isRequired,
    fieldPathPrefix: PropTypes.string.isRequired,
    pathRegex: PropTypes.instanceOf(RegExp),
    possibleValueDomain: PropTypes.string.isRequired,
    normalValueDomain: PropTypes.string,
    values: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        lower: PropTypes.number.isRequired,
        lowerIsInclusive: PropTypes.bool.isRequired,
        upper: PropTypes.number.isRequired,
        upperIsInclusive: PropTypes.bool.isRequired,
    })).isRequired,
};

export default RealValuesEditor;
