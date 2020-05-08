import React from 'react';
import ReactDOM from 'react-dom';
import KnowledgeTree from './KnowledgeTree';

const root = document.getElementById('root');
const data = JSON.parse(root.getAttribute('data-json-encoded'));

ReactDOM.render(<KnowledgeTree {...data} />, root);
