#!/usr/bin/env node
/**
 * Simple income seeder for local development.
 * Usage: node scripts/seed_incomes.cjs
 * It reads DB config from environment variables or falls back to common defaults.
 */
const mysql = require('mysql2/promise')
const { randomUUID } = require('crypto')

async function main() {
  const DB_HOST = process.env.DB_HOST || '127.0.0.1'
  const DB_PORT = process.env.DB_PORT || 3306
  const DB_USER = process.env.DB_USERNAME || process.env.DB_USER || 'root'
  const DB_PASS = process.env.DB_PASSWORD || process.env.DB_PASS || ''
  const DB_NAME = process.env.DB_DATABASE || 'thunder_expense_tracker'

  const conn = await mysql.createConnection({ host: DB_HOST, port: DB_PORT, user: DB_USER, password: DB_PASS, database: DB_NAME })

  console.log('Connected to', DB_HOST, DB_PORT, DB_NAME)

  const incomesToInsert = []
  const categories = ['Salary', 'Interest', 'Bonus', 'Freelance', 'Other']
  const names = ['NCIP', 'Acme Corp', 'Client A', 'Project X', 'Savings']

  for (let i = 0; i < 15; i++) {
    const id = randomUUID()
    const amount = (Math.random() * 2000 + 200).toFixed(2)
    const category = JSON.stringify([categories[Math.floor(Math.random() * categories.length)]])
    const name = names[Math.floor(Math.random() * names.length)]
    // Distribute transaction dates in early Jan 2026
    const day = 1 + Math.floor(Math.random() * 20)
    const transaction_date = `2026-01-${String(day).padStart(2,'0')}`
    const created_at = transaction_date + ' 10:00:00'

    incomesToInsert.push([id, amount, category, name, transaction_date, created_at])
  }

  const sql = `INSERT INTO incomes (id, amount, category, name, transaction_date, created_at) VALUES ?`;
  try {
    const [result] = await conn.query(sql, [incomesToInsert])
    console.log('Inserted rows:', result.affectedRows)
  } catch (err) {
    console.error('Insert failed:', err.message)
  } finally {
    await conn.end()
  }
}

main().catch(err => {
  console.error(err)
  process.exit(1)
})
