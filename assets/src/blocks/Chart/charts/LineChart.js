import * as d3 from 'd3';
import {useChartContext} from '../ChartFrontend';

const {useEffect, memo} = wp.element;

const LineChart = memo(() => {
  const {dataset, chart, width, height, margin, axis} = useChartContext();

  useEffect(() => {
    if (dataset && chart) {
      // Define date format for parsing dates
      const parseTime = d3.timeParse('%Y-%m-%d'); // 2023-01-01 "%Y-%m 2023-01

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

      // add the Line
      const valueLine = d3.line()
        .x(d => x(d[axis.y.field]))
        .y(d => y(d[axis.x.field]));

      chart.append('path')
        .data([dataset])
        .attr('class', 'line')
        .attr('fill', 'none')
        .attr('stroke', '#6c0')
        .attr('stroke-width', 1.5)
        .attr('d', valueLine);
    }
  }, [dataset, chart, width, height, margin, axis]);

  return <></>;
});

LineChart.displayName = 'LineChart';
export default LineChart;
