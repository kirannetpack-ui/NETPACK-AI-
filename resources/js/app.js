import './bootstrap';
import './pwa';
import './native';
import Alpine from 'alpinejs';
import * as L from 'leaflet';
import 'leaflet/dist/leaflet.css';

window.Alpine = Alpine;
window.L = L;

Alpine.start();
