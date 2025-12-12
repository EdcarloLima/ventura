import http from 'k6/http';

import { sleep } from 'k6';

export const options = {
  iterations: 1000,
};

export default function () {
  
  http.get('http://localhost:8080/api/health');

  sleep(1);
}