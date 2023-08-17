import {CounterFrontend} from './CounterFrontend';
import {hydrateBlock} from '../../functions/hydrateBlock';
import {BLOCK_NAME} from './CounterBlock';

hydrateBlock(BLOCK_NAME, CounterFrontend);
