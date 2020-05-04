import React, { useState } from 'react';
import PropTypes from 'prop-types';
import Malfunction from './Malfunction';

const MalfunctionListForm = (props) => {
    const initialNewId = -1;

    const [malfunctions, setMalfunctions] = useState(props.malfunctions);
    const [updatedIds, setUpdatedIds] = useState(new Set());
    const onChange = ({ target }) => {
        const matches = target.name.match(/^malfunctions\[(-?\d+)]\[(\w+)]$/);
        const id = +matches[1];
        const name = matches[2];

        const malfunctionToChange = malfunctions.find(m => m.id === id);

        let newMalfunctions = [...malfunctions];
        newMalfunctions[newMalfunctions.indexOf(malfunctionToChange)] = {
            ...malfunctionToChange,
            [name]: target.value,
        };
        setMalfunctions(newMalfunctions);

        if (id > initialNewId) {
            setUpdatedIds(prevState => prevState.add(id));
        }
    };

    const [deletedIds, setDeletedIds] = useState(new Set());
    const onDelete = id => {
        setMalfunctions(malfunctions.filter(m => m.id !== id));
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
        setMalfunctions([
            ...malfunctions,
            {
                id: latestNewId,
                name: '',
            },
        ]);
        setLatestNewId(latestNewId - 1);
    };

    return (
        <form method="post">
            {malfunctions.map(malfunction => (
                <div key={malfunction.id} style={{ borderBottom: '1px dashed' }}>
                    <br />
                    <Malfunction {...malfunction} onChange={onChange} onDelete={onDelete} />
                    <br />
                </div>
            ))}

            <input type="hidden" name="updatedIds" value={Array.from(updatedIds).join(',')} />
            <input type="hidden" name="deletedIds" value={Array.from(deletedIds).join(',')} />

            <button onClick={onAdd}>Добавить неисправность</button>
            <br />
            <button>Сохранить</button>
        </form>
    );
};

MalfunctionListForm.propTypes = {
    malfunctions: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
    })).isRequired,
};

export default MalfunctionListForm;
