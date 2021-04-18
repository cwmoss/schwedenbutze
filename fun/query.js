const sanityClient = require('@sanity/client')
const client = sanityClient({
  projectId: 'emjk7lsc',
  dataset: 'production',
  token: '', //process.env.S_TOKEN||'', // or leave blank to be anonymous user
  useCdn: false // `false` if you want to ensure fresh data
})

const now = new Date()
const fnow = [
	now.getFullYear(),
	("0" + (now.getMonth() + 1)).slice(-2),
	("0" + now.getDate()).slice(-2)
	]
	.join('-')

const query = '*[_type == "reservation" && departure >= $now] {arrival, departure}'
const params = {now: fnow}

client.fetch(query, params).then(reservations => {
	console.log("results")
  reservations.forEach(r => {
    console.log(`reserved from ${r.arrival} to ${r.departure}`)
  })

})