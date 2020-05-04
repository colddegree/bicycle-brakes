import React from 'react';
import ReactDOM from 'react-dom';
import Root from './Root';

const root = document.getElementById('root');
const data = JSON.parse(root.getAttribute('data-json-encoded'));

ReactDOM.render(<Root features={data}/>, root);
