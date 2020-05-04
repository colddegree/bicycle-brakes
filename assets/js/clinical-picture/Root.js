import React, { useState } from 'react';
import PropTypes from 'prop-types';
import FeaturePicker from './FeaturePicker';

const Root = ({ malfunctions, allFeatures }) => {
    const [selectedMalfunctionId, setSelectedMalfunctionId] = useState(malfunctions.length > 0 ? malfunctions[0].id : 0);

    if (malfunctions.length === 0) {
        return 'Нет неисправностей';
    }

    return (
        <form method="post">
            <select value={selectedMalfunctionId} onChange={event => setSelectedMalfunctionId(+event.target.value)}>
                {malfunctions.map(m => (
                    <React.Fragment key={m.id}>
                        <option value={m.id}>
                            {m.name} (неисправность #{m.id})
                        </option>
                    </React.Fragment>
                ))}
            </select>

            {malfunctions.map(m => (
                <div key={m.id} hidden={m.id !== selectedMalfunctionId}>
                    <input type="hidden" name={`malfunctions[${m.id}][id]`} value={m.id} />
                    <FeaturePicker
                        malfunctionId={m.id}
                        selectedFeatureIds={m.selectedFeatureIds}
                        allFeatures={allFeatures}
                    />
                    <br />
                </div>
            ))}

            <button>Сохранить</button>
        </form>
    );
};

Root.propTypes = {
    malfunctions: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        selectedFeatureIds: PropTypes.arrayOf(PropTypes.number).isRequired,
    })).isRequired,
    allFeatures: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
    })).isRequired,
};

export default Root;
