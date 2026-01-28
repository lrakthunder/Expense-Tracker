import { defineConfig } from "cypress";

export default defineConfig({
  e2e: {
    baseUrl: 'http://127.0.0.1:8002',
    setupNodeEvents(on, config) {
      // Add MySQL database query task for Cypress
      const mysql = require('mysql2/promise');
      on('task', {
        async queryDb({ query }) {
          const connection = await mysql.createConnection({
            host: 'localhost',
            user: 'root',
            password: 'Gwapoakodiba_123',
            database: 'thunder_expense_tracker', 
          });
          const [rows] = await connection.execute(query);
          await connection.end();
          return rows;
        },
      });
    },
  },
});
