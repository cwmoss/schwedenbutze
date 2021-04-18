When a friend of mine ask me to insert a booking calendar on her website, I thought: Whoha, this is some serious work to do. She pointed me to some old project of our company. This was a booking site for single holiday chalet. We build this in 2008, with php and mysql some jquery and a jquery UI component. I remember my college working on it for months. It had some customer email flows, a boring admin interface, coupons, holiday priceing and later we added paypal payments. But then, she would'nt need most of this stuff -- only a calendar that shows available dates for her new bought house-near-the-lake-holiday-place. Her friends and her family could then post their accomodation wishes. (This is how we do in the twentytwenties ;) So i quickly got excited to try out some of the new headless cms & serverless stuff (serverless actually means: it's running on someone else’s server) that I played with during the last year.

## This is the plan:

1 use sanity.io for website content
2 use sanity.io for booking dates
3 use gridsome for the static generated website
4 find a nice vue calendar/ date-range component
5 use a groq query to fetch occupied dates
6 use a netlify function to post new reservations
7 use github for source code
8 use netlify for hosting & deployment
9 connect her domainname with netlify

## Setup sanity.io for website content

Sanity is a lovely headless CMS, wich comes with a very useful content editing interface called *Sanity Studio* and a very powerful API.
It's easy to get started with a project template. I choose the ["Blog with Gridsome" starter template](https://www.sanity.io/create?template=sanity-io%2Fsanity-template-gridsome-blog)

This starter creates:
* a new github repository with the custom Sanity Studio source in `/studio` and the gridsome source in `/web`
* a new pre configured netlify project
* github hooks for auto-deployment on netlify for the Studio and the Website

I choose a public dataset here, since I think this would be the easiest for a start. We might change that later.
If everything goes right, you can see the websiste under https://{your-project-name}.netlify.app and your studio under https://{your-project-name}-studio.netlify.app

## Add Reservation schema to the Sanity Studio

We will need a new document type *reservation*. we create a new file `studio/schemas/documents/reservation.js`

```javascript

export default {
  name: 'reservation',
  type: 'document',
  title: 'Reservations',
  fields: [
     {
      name: 'arrival',
      type: 'date',
      title: 'Arrival',
      description: 'Day of Arrival',
      validation: Rule => Rule.required()
    },
    {
      name: 'departure',
      type: 'date',
      title: 'Departure',
      description: 'Day of Departure',
      validation: Rule => Rule.required().custom((end, context) => {
        var arrival = context.document.arrival
        return (end>arrival)?true:"Please select a departure day that is after the day of arrival"
      })
    },
    {
      name: 'name',
      type: 'string',
      title: 'Name',
      validation: Rule => Rule.required().max(50)
    },
    {
      name: 'email',
      type: 'string',
      title: 'Email Address',
      validation: Rule => Rule.required().regex(
        /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/,
      {
        name: "email", // Error message is "Does not match email-pattern"
        invert: false // Boolean to allow any value that does NOT match pattern
      })
    },
    {
      name: 'message',
      type: 'text',
      title: 'Message',
      validation: Rule => Rule.max(1000)
    },
  ],
  orderings: [
    {
      name: 'arrivalDateAsc',
      title: 'Arrival date new–>old',
      by: [
        {
          field: 'arrival',
          direction: 'asc'
        }
      ]
    },
    {
      name: 'nameDateAsc',
      title: 'Name',
      by: [
        {
          field: 'Name',
          direction: 'asc'
        }
      ]
    }
  ],
  preview: {
    select: {
      name: 'name',
      arrival: 'arrival',
      departure: 'departure'
    },
    prepare ({name, arrival, departure}) {
      return {
        title: name,
        subtitle: arrival + ' - ' + departure
      }
    }
  }
}
```

*Photo Credit*: welcometothesky (on flickr)