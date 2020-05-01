import React from 'react';
import * as types from './types'

const Feature = ({ id, name, type, onChange }) => {
    return (
        <>
            <input name="name" value={name} data-id={id} onChange={onChange} maxLength={255} size={100}/>{' '}
            <select name="type" value={type} data-id={id} onChange={onChange}>
                {Object.values(types).map(type => (
                    <option key={type.id} value={type.id}>{type.name}</option>
                ))}
            </select>
        </>
    );
};

export default Feature;
