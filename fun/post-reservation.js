
const sanityClient = require('@sanity/client')
const client = sanityClient({
  projectId: 'emjk7lsc',
  dataset: 'production',
  token: process.env.S_TOKEN, 
  useCdn: false 
})


const doc = {
  _type: 'reservation',
  name: 'Robert',
  arrival: '2021-04-25',
  departure: '2021-04-25',
  message: 'ick freue mir'
}

client.create(doc)
.then(res => {
  console.log(`reservation created document ID is ${res._id}`)
})
.catch(err => {
  console.error('Oh no, the post failed: ', err.message)
})
