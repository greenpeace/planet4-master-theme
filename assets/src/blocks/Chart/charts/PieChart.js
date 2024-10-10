import * as d3 from 'd3';
import {useChartContext} from '../ChartFrontend';

const {useEffect, memo} = wp.element;

const PieChart = memo(({isDonut}) => {
  const {dataset, chart, width, height, margin} = useChartContext();

  useEffect(() => {
    if (dataset && chart) {
      const data = [10, 29, 39, 23];
      const radius = Math.min(width, height) / 2;
      const colorScale = d3.scaleOrdinal(['#d9f1c5', '#80d643', '#66cc00', '#b9e696']);

      chart
        .attr('transform', `translate(${radius + margin.left},${radius + margin.right})`)
        .attr('width', width)
        .attr('height', height)
        .append('g')
        .attr('transform', `translate(0, ${height})`);

      const pie = d3.pie()
        .value(d => d).sort(null);

      const arc = d3.arc()
        .outerRadius(radius)
        .innerRadius(isDonut ? radius / 2 : 0);

      const group = chart.selectAll('.arc')
        .data(pie(data))
        .enter().append('g')
        .attr('class', 'arc');

      group.append('path')
        .attr('d', arc)
        .attr('class', 'arc')
        .style('fill', (d, i) => colorScale(i))
        .style('stroke', '#434343')
        .style('stroke-width', 4);
    }
  }, [dataset, chart, width, height, margin, isDonut]);

  return <></>;
});

PieChart.displayName = 'PieChart';
export default PieChart;
