import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
  vus: 10,
  duration: '10m30s',
};

export default function() {
  let res = http.get('http://localhost:8080/api/health');
  check(res, { "status is 200": (res) => res.status === 200 });
  sleep(1);
}
