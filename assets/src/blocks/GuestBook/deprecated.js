import metadata from './block.json';
import {frontendRendered} from '../frontendRendered';

const v1 = {
  save: frontendRendered(metadata.name),
};

export default [v1];
