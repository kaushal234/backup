import axios from 'axios'

const httpClientConfig = {
  baseURL: process.env.NODE_ENV === 'development' ? 'http://localhost:8080' : 'https://api.tld-group.com',
  headers: {
    'Accept': 'application/ld+json'
  }
}
const client = axios.create(httpClientConfig)

export { httpClientConfig, client }
