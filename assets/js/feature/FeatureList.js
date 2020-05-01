import React, { useState } from 'react';
import PropTypes from 'prop-types';
import Feature from "./Feature";

const FeatureList = (props) => {
    const [features, setFeatures] = useState(props.features);

    const onChange = ({ target }) => {
        const id = +target.getAttribute('data-id');

        const featureToChange = features.find(feature => feature.id === id);

        const name = target.name;
        let value = target.value;
        if (name === 'type') {
            value = +value;
        }

        let newFeatures = [...features];
        newFeatures[newFeatures.indexOf(featureToChange)] = {
            ...featureToChange,
            [name]: value,
        };
        setFeatures(newFeatures);
    };

    return features.map(feature => (
        <div key={feature.id}>
            <Feature {...feature} onChange={onChange} />
            <input type="hidden" name={`name[${feature.id}]`} value={feature.name} />
            <input type="hidden" name={`type[${feature.id}]`} value={feature.type} />
        </div>
    ));
};

FeatureList.propTypes = {
    features: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        type: PropTypes.number.isRequired,
    })).isRequired,
};

export default FeatureList;
