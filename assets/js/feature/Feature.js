import React from 'react';
import * as types from './types'

const Feature = ({ id, name, type, onChange, onDelete }) => {
    return (
        <div>
            Признак {id < 0 ? '(новый)' : `#${id}`}<br />
            <input type="hidden" name={`features[${id}][id]`} value={id} />

            Название:<br />
            <input
                type="text"
                name={`features[${id}][name]`}
                value={name}
                onChange={onChange}
                maxLength={255}
                size={100}
                required={true}
            />
            <br />

            Тип:<br />
            <select name={`features[${id}][type]`} value={type} onChange={onChange}>
                {Object.values(types).map(type => (
                    <option key={type.id} value={type.id}>{type.name}</option>
                ))}
            </select>
            <br />

            <button onClick={() => onDelete(id)}>Удалить</button>
        </div>
    );
};

export default Feature;
