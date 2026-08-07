/**
 * Logs data to Sentry.
 *
 * @param {Error|string} message        The error to report, or a string message to log.
 * @param {Object|null}  [extraMessage] Optional extra context.
 *
 * @return {void}
 */
/* eslint-disable no-undef */
export function logDataInSentry(message, extraMessage = null)
{
  if (typeof Sentry === 'undefined') {
    return;
  }

  if (typeof message === 'string') {
    Sentry.captureMessage(message, extraMessage);
  } else {
    Sentry.captureException(message, extraMessage);
  }
}
/* eslint-enable no-undef */
