/**
 * REST client.
 *
 * No @wordpress/api-fetch: everything WordPress needs to tell us arrives on
 * window.decentCore, and a nonce header plus fetch is the whole contract.
 */

const boot = window.decentCore || {};

export const config = {
  restUrl: boot.restUrl || '',
  nonce: boot.nonce || '',
  schema: boot.schema || {},
  tabs: boot.tabs || {},
  widgets: boot.widgets || {},
  system: boot.system || {},
  settings: boot.settings || {},
  docsUrl: boot.docsUrl || '',
};

/**
 * Sends a request to the plugin's settings endpoint.
 *
 * @param {string} method HTTP method.
 * @param {Object} [body] JSON payload.
 * @returns {Promise<Object>} Parsed response.
 */
async function request(method, body) {
  const response = await fetch(config.restUrl, {
    method,
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      // The capability check happens server-side; this proves the request
      // came from the logged-in session rather than another origin.
      'X-WP-Nonce': config.nonce,
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(payload.message || `Request failed (${response.status})`);
  }

  return payload;
}

export const api = {
  load: () => request('GET'),
  save: (settings) => request('POST', settings),
};
