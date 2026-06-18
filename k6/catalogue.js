import http from 'k6/http';
import { check, sleep } from 'k6';

// Load test: catalogue browsing (listing + product detail).
// Run: k6 run -e BASE_URL=https://staging.ranga.test k6/catalogue.js

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';

export const options = {
    stages: [
        { duration: '30s', target: 50 },
        { duration: '1m', target: 50 },
        { duration: '30s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'], // Core Web Vitals-aligned budget.
        http_req_failed: ['rate<0.01'],
    },
};

export default function () {
    const list = http.get(`${BASE_URL}/api/v1/products?per_page=24`);
    check(list, { 'product list 200': (r) => r.status === 200 });

    const body = list.json();
    const slug = body && body.data && body.data.length ? body.data[0].slug : null;

    if (slug) {
        const detail = http.get(`${BASE_URL}/api/v1/products/${slug}`);
        check(detail, { 'product detail 200': (r) => r.status === 200 });
    }

    sleep(1);
}
