import React, {useEffect, useState} from 'react';
import PropTypes from 'prop-types';
import 'antd/dist/antd.css';
import { Steps, Button, Checkbox, Select, Radio, InputNumber } from 'antd';
import { LeftOutlined, RightOutlined } from '@ant-design/icons';
import {INT, REAL, SCALAR} from "../feature/types";
import {INT_MAX, INT_MIN, REAL_MAX, REAL_MIN} from "../feature-possible-value/constraints";

const radioStyle = {
    display: 'block',
    height: '30px',
    lineHeight: '30px',
};

const checkboxStyle = {
    ...radioStyle,
    marginLeft: '8px',
};

const Root = ({ features }) => {
    const [currentStep, setCurrentStep] = useState(0);
    const [currentFeatureId, setCurrentFeatureId] = useState(null);

    const [selectedFeatureIdsMap, setSelectedFeatureIdsMap] = useState(features.reduce(
        (idsMap, f) => {
            idsMap[f.id] = false;
            return idsMap;
        },
        {},
    ));
    const onFeatureCheck = (featureId, checked) => {
        setSelectedFeatureIdsMap(prevState => {
            return {
                ...prevState,
                [featureId]: checked,
            };
        });
    };

    const [featureIdToValueMap, setFeatureIdToValueMap] = useState({});

    useEffect(() => {
        const selectedFeatureEntries = Object.entries(selectedFeatureIdsMap).filter(([_, checked]) => checked);

        if (selectedFeatureEntries.length < 1) {
            setCurrentFeatureId(null);
            return;
        }

        const firstSelectedFeatureId = +selectedFeatureEntries[0][0];
        setCurrentFeatureId(firstSelectedFeatureId);

        let newFeatureIdToValueMap = {};
        for (const entry of selectedFeatureEntries) {
            const id = +entry[0];
            const feature = features.filter(f => f.id === id)[0];
            if (feature.type === SCALAR.id && feature.possibleScalarValues.length > 0) {
                newFeatureIdToValueMap[id] = feature.possibleScalarValues[0].id;
            } else if (feature.type === INT.id) {
                newFeatureIdToValueMap[id] = 0;
            } else if (feature.type === REAL.id) {
                newFeatureIdToValueMap[id] = 0.0;
            }
        }
        setFeatureIdToValueMap(newFeatureIdToValueMap);

    }, [selectedFeatureIdsMap]);

    const onChangeFeatureValue = (featureId, value) => {
        setFeatureIdToValueMap(prevState => {
            return {
                ...prevState,
                [featureId]: value,
            }
        })
    };

    const currentFeature = features.filter(f => f.id === currentFeatureId)[0];
    return <>
        <Steps current={currentStep}>
            <Steps.Step title="Выберите признаки для ввода исходных данных" />
            <Steps.Step title="Введите значения признаков" />
        </Steps>
        <br />

        <Button
            onClick={() => setCurrentStep(currentStep - 1)}
            disabled={currentStep <= 0}
            icon={<LeftOutlined />}
        >
            Назад
        </Button>
        {' '}
        <Button
            onClick={() => setCurrentStep(currentStep + 1)}
            disabled={currentStep >= 1 || currentFeatureId === null}
            icon={<RightOutlined />}
        >
            Вперёд
        </Button>
        <br />
        <br />

        {currentStep === 0 && features.map(f => (
            <Checkbox
                key={f.id}
                checked={selectedFeatureIdsMap[f.id]}
                onChange={event => onFeatureCheck(f.id, event.target.checked)}
                style={checkboxStyle}
            >
                {f.name} (#{f.id})
            </Checkbox>
        ))}

        {currentStep === 1 && (
            <>
                <label>
                    Признак
                    <Select
                        defaultValue={currentFeatureId}
                        onChange={value => setCurrentFeatureId(+value)}
                        style={{ width: '100%' }}
                    >
                        {features.filter(f => selectedFeatureIdsMap[f.id]).map(f => (
                            <Select.Option key={f.id} value={f.id}>{f.name} (#{f.id})</Select.Option>
                        ))}
                    </Select>
                </label>
                <br />
                <br />

                {currentFeature.type === SCALAR.id ? (
                    <>
                        <p style={{ marginBottom: 0 }}>Выберите значение:</p>
                        <Radio.Group
                            value={featureIdToValueMap[currentFeatureId]}
                            onChange={event => onChangeFeatureValue(currentFeatureId, event.target.value)}
                        >
                            {currentFeature.possibleScalarValues.map(v => (
                                <Radio key={v.id} value={v.id} style={radioStyle}>{v.value}</Radio>
                            ))}
                        </Radio.Group>
                    </>
                ) : currentFeature.type === INT.id ? (
                    <>
                        <p>Область возможных значений: {currentFeature.possibleValueDomain}</p>
                        <p style={{ marginBottom: 0 }}>Введите значение:</p>
                        <InputNumber
                            type="number"
                            min={INT_MIN}
                            max={INT_MAX}
                            value={featureIdToValueMap[currentFeatureId]}
                            onChange={value => onChangeFeatureValue(currentFeatureId, Math.round(+value))}
                            style={{ width: '100%' }}
                        />
                    </>
                ) : currentFeature.type === REAL.id ? (
                    <>
                        <p>Область возможных значений: {currentFeature.possibleValueDomain}</p>
                        <p style={{ marginBottom: 0 }}>Введите значение:</p>
                        <InputNumber
                            type="number"
                            min={REAL_MIN}
                            max={REAL_MAX}
                            value={featureIdToValueMap[currentFeatureId]}
                            step={0.01}
                            onChange={value => onChangeFeatureValue(currentFeatureId, +value)}
                            style={{ width: '100%' }}
                        />
                    </>
                ) : (
                    <p>Неизвестный тип</p>
                )}

                <form method="post" style={{ marginTop: '24px' }}>
                    {Object.entries(featureIdToValueMap).map(([id, value]) => (
                        <input key={id} type="hidden" name={id} value={value} />
                    ))}

                    <Button type="primary" htmlType="submit">Определить неисправность</Button>
                </form>
            </>
        )}
    </>;
};

Root.propTypes = {
    features: PropTypes.arrayOf(PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        type: PropTypes.number.isRequired,
        possibleScalarValues: PropTypes.arrayOf(PropTypes.shape({
            id: PropTypes.number.isRequired,
            value: PropTypes.string.isRequired,
        })),
        possibleValueDomain: PropTypes.string,
    })).isRequired,
};

export default Root;
