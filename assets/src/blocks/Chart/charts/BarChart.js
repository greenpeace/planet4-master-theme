import * as d3 from 'd3';
import {useChartContext} from '../ChartFrontend';

const {useEffect, memo} = wp.element;

const BarChart = memo(() => {
  const {dataset, chart, width, height, margin, axis} = useChartContext();

  useEffect(() => {
    if (dataset && chart) {
      // X axis
      const x = d3
        .scaleBand()
        .range([0, width])
        .domain(dataset.map(d => d[axis.x.field]))
        .padding(0.2);

      // line axis
      chart
        .attr('transform', `translate(${margin.left},${margin.top})`)
        .append('g')
        .attr('transform', `translate(0, ${height})`)
        .call(d3.axisBottom(x))
        .selectAll('text')
        .attr('transform', 'translate(-10,0)rotate(-45)')
        .style('text-anchor', 'end');

      // Add Y axis
      const domains = dataset.map(d => parseFloat(d[axis.y.field])).sort();
      const y = d3.scaleLinear().domain([Math.min(...domains), Math.max(...domains)]).range([height, 0]);
      chart.append('g').call(d3.axisLeft(y));

      // Bars
      chart
        .selectAll('bar')
        .data(dataset)
        .join('rect')
        .attr('x', d => x(d[`${axis.x.field}`]))
        .attr('y', d => y(d[`${axis.y.field}`]))
        .attr('width', x.bandwidth())
        .attr('height', d => height - y(d[`${axis.y.field}`]))
        .attr('fill', '#80d643');
    }
  }, [dataset, chart, width, height, margin, axis]);

  return <></>;
});

BarChart.displayName = 'BarChart';
export default BarChart;
