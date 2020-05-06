import React from 'react';
import * as types from './types'

const Feature = ({ id, name, type, onChange, onDelete }) => {
    const isNew = id < 0;

    return (
        <div>
            Признак {isNew ? '(новый)' : `#${id}`}<br />
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
            <select
                name={`features[${id}][type]`}
                value={type}
                onChange={onChange}
                disabled={!isNew}
            >
                {Object.values(types).map(type => (
                    <option key={type.id} value={type.id}>{type.name}</option>
                ))}
            </select>
            {!isNew && (
                <>
                    {' '}
                    <span style={{ color: '#AAA' }}>пересоздайте признак, чтобы сменить тип</span>
                </>
            )}
            <br />
            <br />

            <button onClick={() => onDelete(id)}>Удалить</button>
        </div>
    );
};

export default Feature;
