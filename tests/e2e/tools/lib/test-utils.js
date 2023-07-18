import * as path from 'path';
import {test as wpTest, expect, RequestUtils} from '@wordpress/e2e-test-utils-playwright';
import {users} from './users.js';

const storagePath = user => {
  return process.env.STORAGE_STATE_PATH ||
      path.join(process.cwd(), `playwright/.auth/${user?.username || 'user'}.json`);
};

const test = wpTest.extend({
  requestUtils: [
    async ({}, use, workerInfo) => { //NOSONAR
      const requestUtils = await RequestUtils.setup({
        user: users.admin,
        baseURL: workerInfo.project.use.baseURL,
        storageStatePath: storagePath(users.admin),
      });

      await use(requestUtils);
    },
    {scope: 'worker', auto: true},
  ],
});

test.useAdminLoggedIn = () => {
  test.use({storageState: storagePath(users.admin)});
};

export {test, expect, storagePath};
