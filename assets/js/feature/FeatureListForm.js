import React, { useState } from 'react';
import PropTypes from 'prop-types';
import Feature from './Feature';
import { TYPE_SCALAR } from './types';

const FeatureListForm = (props) => {
    const initialNewId = -1;

    const [features, setFeatures] = useState(props.features);
    const [updatedIds, setUpdatedIds] = useState(new Set());
    const onChange = ({ target }) => {
        const matches = target.name.match(/^features\[(-?\d+)]\[(\w+)]$/);
        const id = +matches[1];
        const name = matches[2];

        const featureToChange = features.find(f => f.id === id);

        const value = name !== 'type' ? target.value : +target.value;

        let newFeatures = [...features];
        newFeatures[newFeatures.indexOf(featureToChange)] = {
            ...featureToChange,
            [name]: value,
        };
        setFeatures(newFeatures);

        if (id > initialNewId) {
            setUpdatedIds(prevState => prevState.add(id));
        }
    };

    const [deletedIds, setDeletedIds] = useState(new Set());
    const onDelete = id => {
        setFeatures(features.filter(f => f.id !== id));
        if (id > initialNewId) {
            setDeletedIds(prevState => prevState.add(id));
            setUpdatedIds(prevState => {
                prevState.delete(id);
                return prevState;
            });
        }
    };

    const [latestNewId, setLatestNewId] = useState(initialNewId);
    const onAdd = event => {
        event.preventDefault();
        setFeatures([
            ...features,
            {
                id: latestNewId,
                name: '',
                type: TYPE_SCALAR.id,
            },
        ]);
        setLatestNewId(latestNewId - 1);
    };

    return (
        <form method="post">
            {features.map(feature => (
                <div key={feature.id} style={{ borderBottom: '1px dashed' }}>
                    <br />
                    <Feature {...feature} onChange={onChange} onDelete={onDelete} />
                    <br />
                </div>
            ))}

            <input type="hidden" name="updatedIds" value={Array.from(updatedIds).join(',')} />
            <input type="hidden" name="deletedIds" value={Array.from(deletedIds).join(',')} />

            <button onClick={onAdd}>Добавить признак</button>
            <br />
            <button>Сохранить</button>
        </form>
    );
};

FeatureListForm.propTypes = {
    features: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        type: PropTypes.number.isRequired,
    })).isRequired,
};

export default FeatureListForm;
