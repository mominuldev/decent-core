import { createRoot } from 'react-dom/client';
import App from './App';

const mount = document.getElementById('pixelomatic-core-app');

if (mount) {
  createRoot(mount).render(<App />);
}
