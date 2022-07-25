"""
How to create a simple REST API with Python and Flask in 5 minutes
https://medium.com/duomly-blockchain-online-courses/how-to-create-a-simple-rest-api-with-python-and-flask-in-5-minutes-94bb88f74a23
https://pythonbasics.org/flask-tutorial-routes/
run : pip install flask
Testing :
URL : http://127.0.0.1:8080/api/apple/30
output on browser :
{
  "para1": "apple",
  "para2": "30"
}
"""

from flask import Flask, request

app = Flask(__name__)


@app.route("/api/discountCalculator", methods=['GET'])
def process():
    # processing of request data goes here ...
    total = int(request.args.get('discount'))
    if total >= 10000:
        return str(total - total * 0.12)
    elif total >= 5000:
        return str(total - total * 0.08)
    elif total >= 3000:
        return str(total - total * 0.03)
    else:
        return str(total)


if __name__ == "__main__":
    app.run(debug=True,
            host='127.0.0.1',
            port=8080)
