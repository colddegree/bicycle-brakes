import React from 'react';
import PropTypes from 'prop-types';
import { Tree } from 'antd';
import 'antd/dist/antd.css';
import { CloseCircleTwoTone, CheckCircleTwoTone } from '@ant-design/icons';

const Ok = () => <CheckCircleTwoTone twoToneColor="#5ace00" />;
const Fail = () => <CloseCircleTwoTone twoToneColor="#ff5268" />;

const featureIsOk = feature => {
    return featureHasPossibleValues(feature)
        && featureHasNormalValues(feature)
        && featureNormalValuesAreSubsetOfPossibleValues(feature);
};

const featureHasPossibleValues = feature => {
    return feature.possibleValues.values.length > 0;
};

const featureHasNormalValues = feature => {
    return feature.normalValues.values.length > 0;
};

const featureNormalValuesAreSubsetOfPossibleValues = feature => {
    return feature.normalValues.values.reduce((acc, v) => acc = acc && v.isSubsetOfPossibleValues, true);
};

const KnowledgeTree = ({ features, malfunctions }) => {
    const treeData = [
        {
            title: 'Знания',
            key: 'root',
            children: [
                {
                    title: 'Признаки',
                    key: 'features',
                    icon: !features.reduce((acc, f) => acc = acc && featureIsOk(f), true) ? <Fail /> : <Ok />,
                    children: features.map(f => ({
                        title: f.name,
                        key: f.id,
                        icon: !featureIsOk(f) ? <Fail /> : <Ok />,
                        children: [
                            {
                                title: !featureHasPossibleValues(f)
                                    ? <>Возможные значения <i>[значения отсуствуют]</i></>
                                    : 'Возможные значения',
                                key: `pvs_${f.id}`,
                                icon: !featureHasPossibleValues(f) ? <Fail /> : <Ok />,
                                children: !featureHasPossibleValues(f)
                                        ? null
                                        : [
                                            {
                                                title: f.possibleValues.summary,
                                                key: `pvs_${f.id}_summary`,
                                                children: f.possibleValues.values.map(v => ({
                                                    title: v,
                                                })),
                                            }
                                        ],
                            },
                            {
                                title: !featureHasNormalValues(f)
                                    ? <>Нормальные значения <i>[значения отсуствуют]</i></>
                                    : !featureNormalValuesAreSubsetOfPossibleValues(f)
                                        ? <>Нормальные значения <i>[не являются подмножеством возможных значений]</i></>
                                        : 'Нормальные значения',
                                key: `nvs_${f.id}`,
                                icon: !featureHasNormalValues(f) || !featureNormalValuesAreSubsetOfPossibleValues(f)
                                    ? <Fail />
                                    : <Ok />,
                                children: f.normalValues.values.length === 0
                                    ? null
                                    : [
                                        {
                                            title: f.normalValues.summary,
                                            key: `nvs_${f.id}_summary`,
                                            icon: !featureNormalValuesAreSubsetOfPossibleValues(f) ? <Fail /> : <Ok />,
                                            children: f.normalValues.values.map(v => ({
                                                title: v.value,
                                                icon: !v.isSubsetOfPossibleValues ? <Fail/> : <Ok />
                                            })),
                                        },
                                ],
                            }
                        ],
                    })),
                },
                // TODO: malfunctions
            ],
        },
    ];

    return <Tree
        defaultExpandAll
        treeData={treeData}
        showIcon
        selectable={false}
    />;
};

KnowledgeTree.propTypes = {
    features: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        type: PropTypes.number.isRequired,
        possibleValues: PropTypes.shape({
            summary: PropTypes.string.isRequired,
            values: PropTypes.arrayOf(PropTypes.string).isRequired,
        }).isRequired,
        normalValues: PropTypes.shape({
            summary: PropTypes.string.isRequired,
            values: PropTypes.arrayOf(PropTypes.shape({
                value: PropTypes.string.isRequired,
                isSubsetOfPossibleValues: PropTypes.bool.isRequired,
            })).isRequired,
        }).isRequired,
    })).isRequired,
    malfunctions: PropTypes.arrayOf(PropTypes.shape({
        // TODO
    })).isRequired,
};

export default KnowledgeTree;
