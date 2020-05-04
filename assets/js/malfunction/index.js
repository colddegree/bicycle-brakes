import React from 'react';
import ReactDOM from 'react-dom';
import MalfunctionListForm from './MalfunctionListForm';

const root = document.getElementById('root');
const data = JSON.parse(root.getAttribute('data-json-encoded'));

ReactDOM.render(<MalfunctionListForm malfunctions={data}/>, root);
