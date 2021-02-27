import * as util from '../../util'

// import {format, add, parse} from 'date-fns'

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
        // falls mal mehr als 1 tag abstand
        // gebraucht wird
        //
        // var start = parse(arrival, 'yyyy-MM-dd', new Date())
        // format(add(start, {days:4}), 'yyyy-MM-dd')

        console.log("++ departure ok?", end, arrival)
        return (end>arrival)?true:"Please select a departure day that is after the day of arrival"

        // format(add(Rule.valueOfField('arrival'), {days:1}), 'YYYY-MM-DD'))
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
      var days = util.days(arrival, departure)
      return {
        title: `${name} (${days})`,
        subtitle: util.format_interval(arrival, departure)
      }
    }
  }
}