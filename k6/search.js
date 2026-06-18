import http from 'k6/http';
import { check, sleep } from 'k6';

// Load test: full-text search (Meilisearch-backed in production).
// Run: k6 run -e BASE_URL=https://staging.ranga.test k6/search.js

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const TERMS = ['saree', 'kurti', 'dress', 'red', 'cotton'];

export const options = {
    stages: [
        { duration: '30s', target: 30 },
        { duration: '1m', target: 30 },
        { duration: '20s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<400'],
        http_req_failed: ['rate<0.01'],
    },
};

export default function () {
    const term = TERMS[Math.floor(Math.random() * TERMS.length)];
    const res = http.get(`${BASE_URL}/api/v1/search?q=${term}`);
    check(res, { 'search 200': (r) => r.status === 200 });
    sleep(1);
}
