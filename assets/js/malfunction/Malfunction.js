import React from 'react';

const Malfunction = ({ id, name, onChange, onDelete }) => {
    return (
        <div>
            Неисправность {id < 0 ? '(новая)' : `#${id}`}<br />
            <input type="hidden" name={`malfunctions[${id}][id]`} value={id} />

            Название:<br />
            <input
                type="text"
                name={`malfunctions[${id}][name]`}
                value={name}
                onChange={onChange}
                maxLength={255}
                size={100}
                required={true}
            />
            <br />

            <button onClick={() => onDelete(id)}>Удалить</button>
        </div>
    );
};

export default Malfunction;
