const mysql = require('mysql2/promise');
const { randomUUID } = require('crypto');

(async () => {
  const config = {
    host: 'localhost',
    user: 'root',
    password: 'Gwapoakodiba_123',
    database: 'thunder_expense_tracker',
  };

  const conn = await mysql.createConnection(config);
  try {
    const [users] = await conn.execute("SELECT id FROM users WHERE email = ? LIMIT 1", ['cpascual@gmail.com']);
    if (!users || users.length === 0) {
      console.error('User not found: cpascual@gmail.com');
      process.exit(1);
    }
    const userId = users[0].id;
    console.log('Found user id:', userId);

    const insertSql = `INSERT INTO expenses (id, name, amount, category, client_id, transaction_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())`;

    const rowsToInsert = 30;
    const startDate = new Date('2025-12-01');
    const endDate = new Date();

    function randomDate(start, end) {
      const d = new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()));
      // format as YYYY-MM-DD
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const dd = String(d.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    }

    for (let i = 0; i < rowsToInsert; i++) {
      const id = randomUUID();
      const name = `seed-cpascual-${Date.now()}-${i}`;
      const amount = Math.round((Math.random() * 4900 + 100) * 100) / 100; // 100.00 - 5000.00
      const categoryJson = JSON.stringify(['Other']);
      const txDate = randomDate(startDate, endDate);

      await conn.execute(insertSql, [id, name, amount, categoryJson, userId, txDate]);
      if ((i+1) % 10 === 0) process.stdout.write(`Inserted ${i+1}/${rowsToInsert}...\n`);
    }

    console.log(`Inserted ${rowsToInsert} expenses for user ${userId}`);
  } catch (err) {
    console.error('Error seeding expenses:', err);
    process.exit(1);
  } finally {
    await conn.end();
  }
})();
