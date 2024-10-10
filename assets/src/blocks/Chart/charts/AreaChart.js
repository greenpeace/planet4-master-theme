import * as d3 from 'd3';
import {useChartContext} from '../ChartFrontend';

const {useEffect, memo} = wp.element;

const AreaChart = memo(() => {
  const {dataset, chart, width, height, margin, axis} = useChartContext();

  useEffect(() => {
    if (dataset && chart) {
      // Define date format for parsing dates
      const parseTime = d3.timeParse('%Y-%m-%d');

      dataset.forEach(d => {
        d[axis.x.field] = +(d[axis.x.field]);

        if (axis.x.type === 'date') {
          d[axis.x.field] = parseTime(d[axis.x.field]);
        }

        if (axis.y.type === 'date') {
          d[axis.y.field] = parseTime(d[axis.y.field]);
        }
      });

      // Add X axis and Y axis
      const x = d3.scaleTime()
        .range([0, width]);

      const y = d3.scaleLinear()
        .range([height, 0]);

      x.domain(d3.extent(dataset, d => d[axis.y.field]));
      y.domain([0, d3.max(dataset, d => d[axis.x.field])]);

      chart
        .attr('transform', `translate(${margin.left},${margin.top})`)
        .append('g')
        .attr('transform', `translate(0, ${height})`)
        .call(d3.axisBottom(x));

      chart
        .append('g')
        .call(d3.axisLeft(y));

      chart.append('path')
        .datum(dataset)
        .attr('fill', '#b9e696')
        .attr('stroke', '#66cc00')
        .attr('stroke-width', 1.5)
        .attr('d', d3.area()
          .x(d => {
            return x(d[axis.y.field]);
          })
          .y0(y(0))
          .y1(d => {
            return y(d[axis.x.field]);
          })
        );
    }
  }, [dataset, chart, width, height, margin, axis]);

  return <></>;
});

AreaChart.displayName = 'AreaChart';
export default AreaChart;
