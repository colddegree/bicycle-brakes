import React from 'react';
import ReactDOM from 'react-dom';
import Root from "./Root";

const root = document.getElementById('root');
// const data = JSON.parse(root.getAttribute('data-json-encoded'));

// TODO: remove static
import data from './data.json';
ReactDOM.render(<Root features={data} />, root);
