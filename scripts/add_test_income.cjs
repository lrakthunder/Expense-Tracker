#!/usr/bin/env node
const mysql = require('mysql2/promise')
const axios = require('axios')
const { randomUUID } = require('crypto')

async function main() {
  const DB_HOST = process.env.DB_HOST || '127.0.0.1'
  const DB_PORT = process.env.DB_PORT || 3306
  const DB_USER = process.env.DB_USERNAME || process.env.DB_USER || 'root'
  const DB_PASS = process.env.DB_PASSWORD || process.env.DB_PASS || ''
  const DB_NAME = process.env.DB_DATABASE || 'thunder_expense_tracker'
  const TEST_EMAIL = 'lrakthunder@gmail.com'
  const APP_BASE = process.env.APP_BASE || 'http://127.0.0.1:8002'

  const conn = await mysql.createConnection({ host: DB_HOST, port: DB_PORT, user: DB_USER, password: DB_PASS, database: DB_NAME })

  try {
    // ensure test user exists; if not, try to create via test helper
    const [rows] = await conn.execute('SELECT id FROM users WHERE email = ? LIMIT 1', [TEST_EMAIL])
    if (!rows.length) {
      console.log('Test user not found in DB, calling test/create-user route...')
      try {
        await axios.post(`${APP_BASE}/test/create-user`)
      } catch (err) {
        console.warn('Could not call /test/create-user:', err.message)
      }
    }

    // requery
    const [rows2] = await conn.execute('SELECT id FROM users WHERE email = ? LIMIT 1', [TEST_EMAIL])
    if (!rows2.length) {
      throw new Error('Test user still not found in DB; please ensure the app is running and /test/create-user is enabled')
    }
    const userId = rows2[0].id
    console.log('Using test user id:', userId)

    const id = randomUUID()
    const amount = (Math.random() * 1000 + 50).toFixed(2)
    const category = JSON.stringify(['Salary'])
    const name = 'Automated Test Income'
    const transaction_date = new Date().toISOString().slice(0,10)
    const created_at = new Date().toISOString().slice(0,19).replace('T',' ')

    const insertSql = 'INSERT INTO incomes (id, amount, category, name, transaction_date, created_at, client_id) VALUES (?, ?, ?, ?, ?, ?, ?)'
    const [res] = await conn.execute(insertSql, [id, amount, category, name, transaction_date, created_at, userId])
    console.log('Inserted income id:', id)
  } finally {
    await conn.end()
  }
}

main().catch(err => { console.error(err); process.exit(1) })
