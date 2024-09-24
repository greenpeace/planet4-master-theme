import * as d3 from 'd3';

const {useCallback, useMemo, useEffect, useReducer} = wp.element;
const {InspectorControls} = wp.blockEditor;
// https://github.com/WordPress/gutenberg/tree/trunk/packages/components/src
const {PanelBody, PanelRow, TextControl, Button, SelectControl, RadioControl} = wp.components;
const {__} = wp.i18n;

import { ChartFrontend } from './ChartFrontend';

const initialState = {
  chartType: '',
  dataType: '',
  dataUrl: '',
  dataset: null,
  width: 400,
  height: 600,
  axisFields: [],
  axis: {
    x: {
      field: '',
      type: '',
    },
    y: {
      field: '',
      type: '',
    },
    z: {
      field: '',
      type: '',
    },
  },
};

const reducer = (state, action) => {
  switch (action.type) {
    case 'UPDATE_ATTRIBUTES_TO_STATE':
      return {...state, ...action.payload};
    case 'SET_CHART_TYPE':
      return {...state, chartType: action.payload};
    case 'SET_DATA_TYPE':
      return {...state, dataType: action.payload};
    case 'SET_DATA_URL':
      return {...state, dataUrl: action.payload};
    case 'SET_WIDTH':
      return {...state, width: action.payload !== '' ? parseInt(action.payload) : 100};
    case 'SET_HEIGHT':
      return {...state, height: action.payload !== '' ? parseInt(action.payload) : 100};
    case 'SET_AXIS':
      const axisKey = Object.keys(action.payload)[0];
      const axis = {
        ...state.axis,
        ...(state.axis[axisKey]) ? {
          [`${axisKey}`]: {
            ...state.axis[axisKey],
            ...action.payload[axisKey],
          }
        } : {
          ...action.payload,
        }
      }
      return {...state, axis: {...axis}};
    case 'SET_AXIS_FIELDS':
      return {...state, axisFields: action.payload};
    case 'SET_DATASET':
      return {...state, dataset: action.payload};
    default: {
      // Do nothing
    }
  }
}

// https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/
// https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/
export default function Edit({ attributes, setAttributes }) {
  const [state, dispatch] = useReducer(reducer, initialState);

  const save = useCallback(evt => {
    evt.preventDefault();
    Object.keys(state).forEach((key) => {
      setAttributes({[`${key}`]: state[key]});
    })
  }, [state, dispatch]);
  
  useEffect(() => {
    (async () => {
      if(state.dataUrl !== '' && state.dataType !== '') {
        if(state.dataType === 'csv') {
          dispatch({type: 'SET_DATASET', payload: (await d3.csv(state.dataUrl))});
        }
        
        if(state.dataType === 'json') {
          dispatch({type: 'SET_DATASET', payload: (await d3.json(state.dataUrl))});
        }
      }
    })()
  }, [
    state.dataType,
    state.dataUrl,
  ]);

  useEffect(() => {
    if(state.dataset && state.dataType !== '') {
      if(state.dataType === 'csv') {
        dispatch({type: 'SET_AXIS_FIELDS', payload: state.dataset.columns});
      }
      
      if (state.dataType === 'json' && state.dataset.length) {
        dispatch({type: 'SET_AXIS_FIELDS', payload: Object.keys(state.dataset[0])});
      }
    }
  }, [state.dataset, state.dataType])

  useEffect(() => {
    dispatch({type: 'UPDATE_ATTRIBUTES_TO_STATE', payload: attributes});
  }, [attributes]);

  return useMemo(() => (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Settings', 'planet4-blocks-backend' ) }>
        <PanelRow>
            <SelectControl
              label={ __( 'Chart Type' ) }
              value={ state.chartType || '' }
              onChange={ ( value ) => dispatch({type: 'SET_CHART_TYPE', payload: value})}
              options={ [
                { value: '', label:  __( 'Select chart type', 'planet4-blocks-backend'), disabled: true },
                { value: 'line', label: 'Line' },
                { value: 'area', label: 'Area' },
                { value: 'bar', label: 'Bar' },
                { value: 'pie', label: 'Pie' },
                { value: 'donut', label: 'Donut' },
              ] }
              __nextHasNoMarginBottom
            />
          </PanelRow>
          <PanelRow>
            <RadioControl
              label={ __('Data Type', 'planet4-blocks-backend') }
              help="The type of the parsed data"
              selected={ state.dataType || ''}
              options={ [
                { label: 'CSV', value: 'csv' },
                { label: 'JSON', value: 'json' },
              ] }
              onChange={( value ) => dispatch({type: 'SET_DATA_TYPE', payload: value})}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={ __('Data URL', 'planet4-blocks-backend') }
              value={ state.dataUrl || ''}
              onChange={ ( value ) => dispatch({type: 'SET_DATA_URL', payload: value})}
            />
          </PanelRow>
          <PanelRow>
            <SelectControl
              label={ __( 'Or dummy data' ) }
              value={ state.dataUrl || '' }
              onChange={ ( value ) => {
                dispatch({type: 'SET_DATA_URL', payload: value});
                
              } }
              options={ [
                { value: '', label:  __( 'Select data', 'planet4-blocks-backend'), disabled: true },
                { value: 'https://raw.githubusercontent.com/holtzy/data_to_viz/master/Example_dataset/3_TwoNumOrdered_comma.csv', label: 'Line (CSV)' },
                { value: 'https://raw.githubusercontent.com/holtzy/data_to_viz/master/Example_dataset/7_OneCatOneNum_header.csv', label: 'Bar (CSV)' },
                { value: 'https://calmcode.io/static/data/bigmac.csv', label: 'Bar II (CSV)' },
                { value: 'https://data.wa.gov/api/views/f6w7-q2d2/rows.csv?accessType=DOWNLOAD', label: 'Bar and Line (CSV)' },
                { value: 'https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_time_series/time_series_covid19_confirmed_global.csv', label: 'Line (CSV)' },
                { value: 'https://restcountries.com/v3.1/all', label: 'Line (JSON)' },
                { value: 'http://www.planet4.test/wp-json/planet4/v1/chart/data?dataType=json&chartType=bar', label: 'Bar (JSON)' },
                { value: 'http://www.planet4.test/wp-json/planet4/v1/chart/data?dataType=json&chartType=line', label: 'Line (JSON)' },
              ] }
              __nextHasNoMarginBottom
            />
          </PanelRow>
          {((state.chartType === 'bar' || state.chartType === 'line' || state.chartType === 'area') && state.axisFields) && <>
            <PanelRow>
              <SelectControl
                label={ __('X axis field mapper', 'planet4-blocks-backend') }
                value={ state.axis.x.field || '' }
                onChange={ ( value ) => dispatch({type: 'SET_AXIS', payload: {x: {field: value}}})}
                options={
                  [
                    { value: '', label:  __( 'Select chart type', 'planet4-blocks-backend'), disabled: false },
                  ].concat(state.axisFields.map(d => ({value: d, label: d}))) }
                __nextHasNoMarginBottom
              />
              <SelectControl
                label={ __('Type of field', 'planet4-blocks-backend') }
                value={ state.axis.x.type || '' }
                onChange={ ( value ) => dispatch({type: 'SET_AXIS', payload: {x: {type: value}}})}
                options={ [
                  { value: '', label:  __( 'Select type of field', 'planet4-blocks-backend'), disabled: false },
                  { label: 'String', value: 'string' },
                  { label: 'Number', value: 'number' },
                  { label: 'Date', value: 'date' },
                ]}
                __nextHasNoMarginBottom
              />
            </PanelRow>
            <PanelRow>
              <SelectControl
                label={ __('Y axis field mapper', 'planet4-blocks-backend') }
                value={ state.axis.y.field || '' }
                onChange={ ( value ) => dispatch({type: 'SET_AXIS', payload: {y: {field: value}}})}
                options={ [
                  { value: '', label:  __( 'Select chart type', 'planet4-blocks-backend'), disabled: false },
                ].concat(state.axisFields.map(d => ({value: d, label: d}))) }
                __nextHasNoMarginBottom
              />
              <SelectControl
                label={ __('Type of field', 'planet4-blocks-backend') }
                value={ state.axis.y.type || '' }
                onChange={ ( value ) => dispatch({type: 'SET_AXIS', payload: {y: {type: value}}})}
                options={ [
                  { value: '', label:  __( 'Select type of field', 'planet4-blocks-backend'), disabled: false },
                  { label: 'String', value: 'string' },
                  { label: 'Number', value: 'number' },
                  { label: 'Date', value: 'date' },
                ]}
                __nextHasNoMarginBottom
              />
            </PanelRow>
            </>}
          <PanelRow>
            <TextControl
              label={ __('Width', 'planet4-blocks-backend') }
              value={state.width}
              onChange={ ( value ) => dispatch({type: 'SET_WIDTH', payload: value})}
            />
          </PanelRow>
          <PanelRow>
            <TextControl
              label={ __('Height', 'planet4-blocks-backend') }
              value={state.height}
              onChange={ ( value ) => dispatch({type: 'SET_HEIGHT', payload: value})}
            />
          </PanelRow>
          <PanelRow>
            <Button onClick={save}>Apply chart settings</Button>
          </PanelRow>
        </PanelBody>
      </InspectorControls>
      
      <ChartFrontend {...attributes}/>
    </>
  ), [
    attributes,
    // dataUrl,
    state,
  ]);
}