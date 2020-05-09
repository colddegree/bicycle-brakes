import React from 'react';
import PropTypes from 'prop-types';
import { Tree } from 'antd';
import 'antd/dist/antd.css';
import { CloseCircleTwoTone, CheckCircleTwoTone } from '@ant-design/icons';

const Ok = () => <CheckCircleTwoTone twoToneColor="#5ace00" />;
const Fail = () => <CloseCircleTwoTone twoToneColor="#ff5268" />;


const featuresAreOk = features => {
    return !featuresAreEmpty(features)
        && features.reduce((acc, f) => acc = acc && featureIsOk(f), true);
};

const featuresAreEmpty = features => {
    return features.length < 1;
};

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


const malfunctionsAreOk = malfunctions => {
    return !malfunctionsAreEmpty(malfunctions)
        && malfunctions.reduce((acc, m) => acc = acc && malfunctionIsOk(m), true);
};

const malfunctionsAreEmpty = malfunctions => {
    return malfunctions.length < 1;
};

const malfunctionIsOk = malfunction => {
    return malfunctionClinicalPictureHasFeatures(malfunction)
        && malfunctionClinicalPictureIsOk(malfunction);
};

const malfunctionClinicalPictureHasFeatures = ({ clinicalPicture }) => {
    return clinicalPicture.length > 0;
};

const malfunctionClinicalPictureIsOk = ({ clinicalPicture }) => {
    return clinicalPicture
        .reduce((acc, f) => acc = acc && malfunctionClinicalPictureFeatureIsOk(f), true);
};

const malfunctionClinicalPictureFeatureIsOk = feature => {
    return !malfunctionFeatureValuesAreEmpty(feature) && malfunctionFeatureValuesAreOk(feature);
};

const malfunctionFeatureValuesAreEmpty = feature => {
    const { values } = feature.malfunctionFeatureValues;
    return values.length < 1;
};

const malfunctionFeatureValuesAreOk = feature => {
    const { values } = feature.malfunctionFeatureValues;
    return values.reduce((acc, v) => acc = acc && v.isSubsetOfPossibleValues && !v.isIntersectsWithNormalValues, true);
};


const KnowledgeTree = ({ features, malfunctions }) => {
    const treeData = [
        {
            title: 'Знания',
            key: 'root',
            icon: featuresAreOk(features) && malfunctionsAreOk(malfunctions) ? <Ok /> : <Fail />,
            children: [
                {
                    title: featuresAreEmpty(features)
                        ? <>Признаки <i>[отсутствуют]</i></>
                        : 'Признаки',
                    key: 'features',
                    icon: featuresAreOk(features) ? <Ok /> : <Fail />,
                    children: features.map(f => ({
                        title: `${f.name} (#${f.id})`,
                        key: `f${f.id}`,
                        icon: !featureIsOk(f) ? <Fail /> : <Ok />,
                        children: [
                            {
                                title: !featureHasPossibleValues(f)
                                    ? <>Возможные значения <i>[значения отсуствуют]</i></>
                                    : 'Возможные значения',
                                key: `f${f.id}_pv`,
                                icon: !featureHasPossibleValues(f) ? <Fail /> : <Ok />,
                                children: !featureHasPossibleValues(f)
                                        ? null
                                        : [
                                            {
                                                title: f.possibleValues.summary,
                                                key: `f${f.id}_pv_s`,
                                                children: f.possibleValues.values.map(v => ({
                                                    title: v.value,
                                                    key: `f${f.id}_pv_s_v${v.id}`,
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
                                key: `f${f.id}_nv`,
                                icon: !featureHasNormalValues(f) || !featureNormalValuesAreSubsetOfPossibleValues(f)
                                    ? <Fail />
                                    : <Ok />,
                                children: f.normalValues.values.length === 0
                                    ? null
                                    : [
                                        {
                                            title: f.normalValues.summary,
                                            key: `f${f.id}_nv_s`,
                                            icon: !featureNormalValuesAreSubsetOfPossibleValues(f) ? <Fail /> : <Ok />,
                                            children: f.normalValues.values.map(v => ({
                                                title: v.value,
                                                key: `f${f.id}_nv_s_v${v.id}`,
                                                icon: !v.isSubsetOfPossibleValues ? <Fail/> : <Ok />
                                            })),
                                        },
                                ],
                            }
                        ],
                    })),
                },
                {
                    title: malfunctionsAreEmpty(malfunctions)
                        ? <>Неисправности <i>[отсутствуют]</i></>
                        : 'Неисправности',
                    key: 'malfunctions',
                    icon: malfunctionsAreOk(malfunctions) ? <Ok /> : <Fail />,
                    children: malfunctions.map(m => ({
                        title: `${m.name} (#${m.id})`,
                        key: `m${m.id}`,
                        icon: malfunctionIsOk(m) ? <Ok /> : <Fail />,
                        children: [
                            {
                                title: !malfunctionClinicalPictureHasFeatures(m)
                                    ? <>Клиническая картина <i>[признаки отсутствуют]</i></>
                                    : 'Клиническая картина',
                                key: `m${m.id}_cc`,
                                icon: !malfunctionClinicalPictureHasFeatures(m) || !malfunctionClinicalPictureIsOk(m)
                                    ? <Fail />
                                    : <Ok />,
                                children: m.clinicalPicture.map(f => ({
                                    title: `${f.name} (#${f.id})`,
                                    key: `m${m.id}_cc_f${f.id}`,
                                    icon: !malfunctionClinicalPictureFeatureIsOk(f) ? <Fail /> : <Ok />,
                                    children: [
                                        {
                                            title: malfunctionFeatureValuesAreEmpty(f)
                                                ? <>Значения <i>[отсутствуют]</i></>
                                                : 'Значения',
                                            key: `m${m.id}_cc_f${f.id}_v`,
                                            icon: malfunctionFeatureValuesAreEmpty(f)
                                                || !malfunctionFeatureValuesAreOk(f) ? <Fail /> : <Ok />,
                                            children: malfunctionFeatureValuesAreEmpty(f)
                                                ? null
                                                : [
                                                    {
                                                        title: f.malfunctionFeatureValues.summary,
                                                        key: `m${m.id}_cc_f${f.id}_v_s`,
                                                        icon: !malfunctionFeatureValuesAreOk(f) ? <Fail /> : <Ok />,
                                                        children: f.malfunctionFeatureValues.values.map(v => ({
                                                            title: !v.isSubsetOfPossibleValues
                                                                ? <>{v.value} <i>[не является подмножеством возможных значений]</i></>
                                                                : v.isIntersectsWithNormalValues
                                                                    ? <>{v.value} <i>[пересекается с нормальными значениями]</i></>
                                                                    : v.value,
                                                            key: `m${m.id}_cc_f${f.id}_v_s_t${f.type}_v${v.id}`,
                                                            icon: !v.isSubsetOfPossibleValues || v.isIntersectsWithNormalValues
                                                                ? <Fail />
                                                                : <Ok />,
                                                        })),
                                                    }
                                                ],
                                        },
                                    ],
                                })),
                            },
                        ],
                    })),
                },
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
            values: PropTypes.arrayOf(PropTypes.shape({
                id: PropTypes.number.isRequired,
                value: PropTypes.string.isRequired,
            })).isRequired,
        }).isRequired,
        normalValues: PropTypes.shape({
            summary: PropTypes.string.isRequired,
            values: PropTypes.arrayOf(PropTypes.shape({
                id: PropTypes.number.isRequired,
                value: PropTypes.string.isRequired,
                isSubsetOfPossibleValues: PropTypes.bool.isRequired,
            })).isRequired,
        }).isRequired,
    })).isRequired,
    malfunctions: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        clinicalPicture: PropTypes.arrayOf(PropTypes.shape({
            id: PropTypes.number.isRequired,
            name: PropTypes.string.isRequired,
            malfunctionFeatureValues: PropTypes.shape({
                summary: PropTypes.string.isRequired,
                values: PropTypes.arrayOf(PropTypes.shape({
                    id: PropTypes.number.isRequired,
                    value: PropTypes.string.isRequired,
                    isSubsetOfPossibleValues: PropTypes.bool.isRequired,
                    isIntersectsWithNormalValues: PropTypes.bool.isRequired,
                })).isRequired,
            }).isRequired,
        })).isRequired,
    })).isRequired,
};

export default KnowledgeTree;
