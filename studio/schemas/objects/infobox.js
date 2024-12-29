export default {
  name: 'infobox',
  type: 'object',
  title: 'Infobox',

  fields: [
    {
      name: 'title',
      type: 'string',
      title: 'Titel',
    },
    {
      name: 'ref',
      type: 'infoboxlink',
      title: 'Infobox',
    }
  ],
  preview: {
    select: {
      title: 'title',
      rtitle: 'ref.title',
    },
    prepare ({title = '-', rtitle}) {
      // const dateSegment = format(publishedAt, 'YYYY/MM')
      return {
        title: title + ' / ' + rtitle,
      }
    }
  }
}
