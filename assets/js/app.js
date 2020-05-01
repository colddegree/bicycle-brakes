// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';

import React, { useState } from 'react';
import ReactDOM from 'react-dom';

const Like = () => {
    const [liked, setLiked] = useState(false);

    if (liked) {
        return 'You liked this.';
    }

    return <button onClick={() => setLiked(true)}>Like</button>;
};

ReactDOM.render(<Like />, document.getElementById('root'));
