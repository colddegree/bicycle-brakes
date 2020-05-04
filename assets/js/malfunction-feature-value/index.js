import React from 'react';
import ReactDOM from 'react-dom';

const root = document.getElementById('root');
const data = JSON.parse(root.getAttribute('data-json-encoded'));

ReactDOM.render(<h1>malfunction-feature-value</h1>, root);
