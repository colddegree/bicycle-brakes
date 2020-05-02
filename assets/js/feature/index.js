import React from 'react';
import ReactDOM from 'react-dom';
import FeatureListForm from './FeatureListForm';

const root = document.getElementById('root');
const data = JSON.parse(root.getAttribute('data-json-encoded'));

ReactDOM.render(<FeatureListForm features={data} />, root);
