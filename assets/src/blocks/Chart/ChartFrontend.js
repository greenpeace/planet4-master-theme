import * as d3 from 'd3';
// Ideally we should import as lazy
import LineChart from './charts/LineChart';
import BarChart from './charts/BarChart';
import PieChart from './charts/PieChart';
import AreaChart from './charts/AreaChart';
//

const {createContext, useContext, useEffect, useMemo, useState, useRef, cloneElement} = wp.element;
const Context = createContext({});

export const ChartFrontend = ({dataType, dataUrl, chartType, width, height, axis}) => {
  const [chart, setChart] = useState();
  const ref = useRef();
  const [dataset, setDataset] = useState();
  const [svg, setSvg] = useState();
  const margin = { top: 30, right: 30, bottom: 70, left: 60 };

  useEffect(() => {
    (async () => {
      if(dataUrl !== '' && dataType !== '') {
        if(dataType === 'csv') {
          setDataset(await d3.csv(dataUrl))
        }
        
        if(dataType === 'json') {
          setDataset(await d3.json(dataUrl))
        }
      }
    })()
  }, [dataType, dataUrl]);
  

  useEffect(() => {
    // Reset charts
    d3.selectAll(`#main-group > *`).remove();

  }, [chartType])

  useEffect(() => {
    if(dataset && chartType) {
      // (async () => {
        switch (chartType){
          case 'line':
            // Do not need to pass props
            setChart(cloneElement(<LineChart />, {}));
            // setChart((await import ('./charts/LineChart')).default);
            break;
          case 'area':
            setChart(cloneElement(<AreaChart />, {}));
            break;
          case 'bar':
            setChart(cloneElement(<BarChart />, {}));
            break;
          case 'pie':
            setChart(cloneElement(<PieChart />, {isDonut: false}));
            break;
          case 'donut':
            setChart(cloneElement(<PieChart />, {isDonut: true}));
            break;
          default:
            null;
        }
    }
    // })();
  }, [chartType, dataset]);

  useEffect(() => {
    if(svg) {
      svg
        .style('background-color', '#f1f1f1')
        .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("id", 'main-group')
    }
  }, [svg])

  useEffect(() => {
    if(ref.current) {
      setSvg(d3.select(ref.current));
    }
  }, [ref])

  return useMemo(() => (
    <Context.Provider 
      value={{
        dataset,
        chart: d3.select(ref.current).select('#main-group'),
        width: width,
        height: height, 
        margin: margin, 
        axis,
      }}>
      <svg
        ref={ref}
        width={width + margin.left + margin.right}
        height={height + margin.top + margin.bottom}
      >{chart}</svg>
    </Context.Provider>
  ), [
    chart,
    svg,
    width,
    height,
    axis,
    ref,
  ]);
}

export const useChartContext = () => useContext(Context);