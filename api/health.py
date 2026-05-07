import json

def handler(request):
    """
    Health check endpoint for Vercel
    """
    return {
        'statusCode': 200,
        'headers': {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*'
        },
        'body': json.dumps({
            'ok': True,
            'status': 'running'
        })
    }
