import React from 'react';
import ReactDOM from 'react-dom';
import FeatureList from './FeatureList';

const root = document.getElementById('root');
const data = JSON.parse(root.getAttribute('data-json-encoded'));

ReactDOM.render(<FeatureList features={data} />, root);
