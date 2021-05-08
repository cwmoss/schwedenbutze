/*
npm install netlify-cli -g
netlify dev
http://localhost:8888/.netlify/functions/fetch-reservations

wie funktioniert das deployment von der kommandozeile?
monorepo?
*/

const sanityClient = require('@sanity/client')
const client = sanityClient({
  projectId: 'emjk7lsc',
  dataset: 'production',
  token: process.env.S_TOKEN||'', // or leave blank to be anonymous user
  useCdn: true // `false` if you want to ensure fresh data
})

exports.handler = async function(event, context) {
  var data
  try {
    data = JSON.parse(event.body)
  } catch (err) {
    console.log(err)
    return {
      statusCode: 500,
      body: JSON.stringify({msg: err.message})
    }
  }

  if(data.auto){
    return {
      statusCode: 200,
      body: JSON.stringify({msg: "ok, spam"})
    }
  }

  if(!(data.name && data.email && data.arrival && data.departure)){
    return {
      statusCode: 500,
      body: JSON.stringify({msg: 'missing information'})
    }
  }

 const doc = {
    _type: 'reservation',
    name: data.name,
    email: data.email,
    arrival: data.arrival,
    departure: data.departure,
    message: data.message
  }

  return client.create(doc)
  .then(res => {
    console.log(`reservation created document ID is ${res._id}`)
    return {
      statusCode: 200,
      body: JSON.stringify({msg: 'ok'})
    };
  })
  .catch(err => {
    console.error('Oh no, the post failed: ', err.message)
    return {
      statusCode: 500,
      body: JSON.stringify({msg: err.message})
    }
  })

}
