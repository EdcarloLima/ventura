import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
  vus: 1000,
  duration: '1m00s',
};

// Função para gerar placa aleatória no formato brasileiro (ABC1234)
function generateRandomPlate() {
  const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const numbers = '0123456789';
  
  let plate = '';
  
  // 3 letras
  for (let i = 0; i < 3; i++) {
    plate += letters.charAt(Math.floor(Math.random() * letters.length));
  }
  
  // 4 números
  for (let i = 0; i < 4; i++) {
    plate += numbers.charAt(Math.floor(Math.random() * numbers.length));
  }
  
  return plate;
}

export default function() {
  const plate = generateRandomPlate();
  
  const payload = JSON.stringify({
    plate: plate,
    gate_id: 'entrada-1'
  });
  
  const params = {
    headers: {
      'Content-Type': 'application/json',
    },
  };
  
  let res = http.post('http://localhost:8080/api/vehicles/entry', payload, params);
  //let res = http.get('http://localhost:8080/api/vehicles/entry/random');
  check(res, { "status is 201": (res) => res.status === 201 });
}
